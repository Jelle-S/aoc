<?php

namespace Jelle_S\AOC\Client;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AOCClient {

  protected HttpClientInterface $client;
  protected Crawler $crawler;

  protected const DAY_URL = 'https://adventofcode.com/%d/day/%d';

  public function __construct() {
    $this->client = HttpClient::create(['headers' => ['Cookie' => sprintf("%s=%s", 'session', $_ENV['AOC_COOKIE'])]]);
    $this->crawler = new Crawler();
  }

  public function getInput(int $year, int $day) {
    return $this->client->request('GET', sprintf(self::DAY_URL . '/input', $year, $day))->getContent();
  }

  public function getChallengeHTML(int $year, int $day, int $part) {
    $html = $this->client->request('GET', sprintf(self::DAY_URL, $year, $day))->getContent();
    $this->crawler->add($html);
    $result = $this->crawler->filter('main > article')->eq($part - 1)->html();
    $this->crawler->clear();

    return $result;
  }

  public function getSample(int $year, int $day) {
    $html = $this->client->request('GET', sprintf(self::DAY_URL, $year, $day))->getContent();
    $this->crawler->add($html);

    $result = $this->crawler
        ->filter('main > article')
        ->eq(0)
        ->filter('p')
        ->reduce(fn($node) => str_contains($node->text(), 'example'))
        ->eq(0)
        ->nextAll()
        ->filter('pre code')
        ->text(normalizeWhitespace: false);

    $this->crawler->clear();

    return $result;
  }

}
