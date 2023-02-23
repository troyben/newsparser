<?php

namespace App\Command;
use App\Entity\News;
use App\Service\Scrapper;
use GuzzleHttp\Exception\GuzzleException;
use http\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Doctrine\Persistence\ManagerRegistry;

class ParseNewsCommand extends Command
{
    protected static $defaultName = 'app:parse-news';

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Parsing news from highload.today');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $scrapper = new Scrapper();
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(News::class);

        try {
            $arrObj = $scrapper->scrape('https://highload.today/category/novosti/');
            $len = count($arrObj['titles']);

            $sanitize_news_items = array();

            for ($i = 0; $i < $len; $i++) {
                $sanitize_news_items[] = [
                    'title' => $arrObj['titles'][$i],
                    'description' => $arrObj['descriptions'][$i],
                    'imgUrl' => $arrObj['images'][$i]
                ];
            }

            foreach ($sanitize_news_items as $item) {
                $findNewsByTitle = $repository->findOneBy(['title' => $item['title']]);
                if ($findNewsByTitle) {
                    $io->text(sprintf('Updating Article "%s": ', $item['title']));
                    $findNewsByTitle->setDescription($item['description']);
                    $findNewsByTitle->setImage($item['imgUrl']);
                }
                else {
                    $io->text(sprintf('Saving: "%s" ', $item['title']));
                    $newNewsItem = new News();
                    $newNewsItem->setTitle($item['title']);
                    $newNewsItem->setDescription($item['description']);
                    $newNewsItem->setImage($item['imgUrl']);
                    $entityManager->persist($newNewsItem);
                }
            }
            $entityManager->flush();
            $io->success("Parsing Completed!");
            return Command::SUCCESS;
        } catch (GuzzleException|TransportExceptionInterface $e) {
            $io->error(sprintf('Error: "%s"', $e));
            return Command::FAILURE;
        }
    }
}