<?php

namespace Jelle_S\AOC\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;

class AOCClient {

  protected Client $client;
  protected Crawler $crawler;

  protected const SESSION_DOMAIN = 'adventofcode.com';
  protected const DAY_URL = 'https://adventofcode.com/%d/day/%d';

  public function __construct() {
    $cookies = CookieJar::fromArray(['session' => $_ENV['AOC_COOKIE']], self::SESSION_DOMAIN);
    $this->client = new Client(['cookies' => $cookies]);
    $this->crawler = new Crawler();
  }

  public function getInput(int $year, int $day) {
    $request = new Request('GET', sprintf(self::DAY_URL . '/input', $year, $day));

    return $this->client->send($request)->getBody()->getContents();
  }

  public function getChallengeHTML(int $year, int $day, int $part) {
    $request = new Request('GET', sprintf(self::DAY_URL, $year, $day));
    $html = $this->client->send($request)->getBody()->getContents();
    $this->crawler->add($html);
    $result = $this->crawler->filter('main > article')->eq($part - 1)->html();
    $this->crawler->clear();

    return $result;
  }

  public function getSample(int $year, int $day) {
    $request = new Request('GET', sprintf(self::DAY_URL, $year, $day));
    $html = $this->client->send($request)->getBody()->getContents();
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
