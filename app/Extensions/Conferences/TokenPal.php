<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Conferences;

use ArrayIterator\Coinvestasi\Core\Cache;
use ArrayIterator\Coinvestasi\Core\Container;
use ArrayIterator\Coinvestasi\Core\Generator\DesktopUserAgent;
use GuzzleHttp\Client;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Class TokenPal
 * @package ArrayIterator\Coinvestasi\Extensions\Conferences
 */
class TokenPal
{
    const URI = 'https://www.tokenpals.io/conferences/';
    protected $data;
    protected $count = 0;

    /**
     * @return array
     */
    public function getData() : array
    {
        if (is_array($this->data)) {
            return $this->data;
        }

        return $this->data = $this->process();
    }

    /**
     * Processing data
     *
     * @return array
     */
    protected function process() : array
    {
        /**
         * @var Cache $cache
         */
        $cache = Container::take('cache');
        $keyCache = sha1(self::URI);
        if ($cache->contains($keyCache)) {
            $cachedData = $cache->get($keyCache);
            if (is_array($cachedData) && !empty($cachedData)) {
                return $cachedData;
            }

            $cache->delete($keyCache);
        }

        $data = $this->getDataURI();
        if ($data === '') {
            return [];
        }

        $pageCrawler = HtmlPageCrawler::create($this->getDataURI());
        $pageCrawler = $pageCrawler->filter('table[data-footable_id][id^=footable] tbody > tr');
        $conferences = [];
        $pageCrawler->each(function (HtmlPageCrawler $crawler) use (&$conferences) {
            $td = $crawler->filter('td');
            if ($td->count() < 4) {
                return;
            }
            $date = trim($td->eq(0)->text());
            $conf = $td->eq(1)->filter('a');
            $country = $td->eq(2)->text();
            $city = $td->eq(3)->text();
            if (!$conf->count() || !preg_match('/20[0-9]+/', $date)) {
                return;
            }
            $conferences[] = [
                'date' => $date,
                'conference' => [
                    'name' => trim($conf->text()),
                    'link' => $conf->attr('href'),
                ],
                'country' => $country,
                'city' => $city,
            ];
        });

        $pageCrawler->clear();
        unset($pageCrawler);
        $cache->save($keyCache, $conferences, 3600);
        return $conferences;
    }

    /**
     * Get data from URL To scrapping API
     *
     * @return string
     */
    protected function getDataURI() : string
    {
        if ($this->count > 5) {
            return '';
        }

        $this->count++;
        $ua = new DesktopUserAgent();
        try {
            $uri = new Client();
            $res = $uri->get(self::URI, [
                'headers' => [
                    'User-Agent' => $ua->chrome(),
                ]
            ]);
            $res = $res->getBody();
            $body = (string) $res;
            $res->close();
            unset($res);
            return $body;
        } catch (\Exception $e) {
            return $this->getDataURI();
        }
    }
}
