<?php
namespace App\Service;
use GuzzleHttp\Exception\GuzzleException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Scrapper {
    public function __construct()
    {
    }
    /**
     * @throws TransportExceptionInterface
     * @throws GuzzleException
     */
    public function scrape($source): array
    {
        $client = new Client([
            'connect_timeout' => 10,
            'timeout'         => 10.00,
            'http_errors'     => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            ]
        ]);

        $response = $client->get($source);
        $tree = new Crawler($response->getBody()->getContents());
        return [
            "titles" => $tree->filterXpath("//div[contains(@class,'lenta-item')]//a//h2/text()")->each(function ($node, $i) {
                return $node->text();
            }),
            "descriptions" => $tree->filterXpath("//div[contains(@class,'lenta-item')]/p/text()")->each(function ($node, $i) {
                return $node->text();
            }),
            "images" => $tree->filterXpath("//div[contains(@class,'lenta-item')]/a/div[contains(@class, 'lenta-image')]/img")->each(function ($node, $i) {
                return $node->attr('data-lazy-src');
            }),
        ];
    }
}