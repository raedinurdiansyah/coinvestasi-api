<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Extensions\Ico\Routes;

use ArrayIterator\Coinvestasi\Core\ApiGroup;
use ArrayIterator\Coinvestasi\Core\Cache;
use ArrayIterator\Coinvestasi\Core\Container;
use ArrayIterator\Coinvestasi\Core\Generator\DesktopUserAgent;
use ArrayIterator\Coinvestasi\Core\Generator\JsonPatent;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Class Calendar
 * @package ArrayIterator\Coinvestasi\Extensions\Ico\Routes
 * @see https://cointelegraph.com/ico-calendar
 */
class Calendar extends ApiGroup
{
    const URI = 'https://cointelegraph.com/ico-calendar';

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param App $slim
     */
    public function __invoke(App $slim)
    {
        parent::__invoke($slim);
        $this->registerRoute($slim);
    }

    /**
     * @param App $slim
     */
    protected function registerRoute(App $slim)
    {
        $slim->get('[/]', [$this, 'getCalendar']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function getCalendar(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        return JsonPatent::success(
            $response,
            $this->getData()
        );
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

    /**
     * @return array
     */
    protected function getData() : array
    {
        if (is_array($this->data)) {
            return $this->data;
        }

        $this->data = [
            'past' => [],
            'ongoing' => [],
            'upcoming' => [],
        ];

        /**
         * @var Cache $cache
         */
        $cache = Container::take('cache');
        $keyCache = sha1(self::URI);
        if ($cache->contains($keyCache)) {
            $cachedData = $cache->get($keyCache);
            if (is_array($cachedData) && !empty($cachedData)
                && array_keys($this->data) === array_keys($cachedData)
                && is_array($cachedData['ongoing'])
                && is_array($cachedData['past'])
                && is_array($cachedData['upcoming'])
            ) {
                return $cachedData;
            }

            $cache->delete($keyCache);
        }

        $data = $this->getDataURI();
        if ($data === '') {
            return $this->data;
        }

        $data = HtmlPageCrawler::create($data);
        $crawler = $data->filter('ico-calendar-search');
        if (! $crawler->count()
            || !($attr = $crawler->attr(':data'))
            || !is_string($attr)
            || trim($attr) === ''
        ) {
            $crawler->clear();
            $cache->save($keyCache, $this->data, 30);
            unset($crawler);
            return $this->data;
        }
        $attr = preg_replace('~(\"\s*\:\s*)\&[qlrd]quot\;~', '$1"', $attr);
        $attr = json_decode($attr, true);
        if (!is_array($attr) || empty($attr['items']) || !is_array($attr['items'])) {
            $crawler->clear();
            unset($attr);
            $cache->save($keyCache, $this->data, 30);
            return $this->data;
        }
        $attr = $attr['items'];
        foreach ($this->data as $key => &$v) {
            if (empty($attr[$key]) || !is_array($attr[$key])) {
                unset($attr[$key]);
                continue;
            }
            foreach ($attr[$key] as $k => $item) {
                unset($attr[$key][$k]);
                if (is_array($item)) {
                    $items = $this->parseDataPerItem($item);
                    if (is_array($items)) {
                        $v[$k] = $items;
                    }
                }
            }
        }

        // save cache
        $cache->save($keyCache, $this->data, 3600*24);
        return $this->data;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function parseDataPerItem(array $data)
    {
        $default = [
            'title',
            'website',
            'site',
            'start_date',
            'end_date',
            'img',
            'small_thumb',
        ];
        $diff = array_diff($default, array_keys($data));
        if (!empty($diff)) {
            return null;
        }
        $date = [];
        foreach ($default as $v) {
            $val = $data[$v];
            if ($v === 'start_date' || $v === 'end_date') {
                if (!isset($val['timezone']) || !isset($val['date'])) {
                    return null;
                }

                $val = $this->convertDate($val);
                $v   = substr($v, 0, -5);
                if (!is_array($val)) {
                    return null;
                }
                if (!isset($date['date'])) {
                    $date['date'] = [];
                }
                $date['date'][$v] = $val;
                continue;
            }
            if ($v === 'img' || $v === 'small_thumb') {
                if (!is_string($val)) {
                    return null;
                }
                $val = preg_replace('~^((?:https?:)?//)~', 'https://', $val);
            }
            $date[$v] = $val;
        }

        return $date;
    }

    /**
     * @param array $date
     *
     * @return array|null
     */
    private function convertDate(array $date)
    {
        try {
            $date = new \DateTime($date['date'], new \DateTimeZone($date['timezone']));
            $utc  = (clone $date)->setTimezone(new \DateTimeZone('UTC'));
            $jkt  = (clone $date)->setTimezone(new \DateTimeZone('Asia/Jakarta'));
        } catch (\Exception $e) {
            return null;
        }
        return [
            'original' => [
                'timezone'  => $date->getTimezone()->getName(),
                'timestamp' => $date->getTimestamp(),
                'date'      => $date->format('Y-m-d H:i:s')
            ],
            'utc' => [
                'timezone' => $utc->getTimezone()->getName(),
                'timestamp' => $utc->getTimestamp(),
                'date'      => $utc->format('Y-m-d H:i:s')
            ],
            'jakarta' => [
                'timezone'   => $jkt->getTimezone()->getName(),
                'timestamp' => $jkt->getTimestamp(),
                'date'      => $jkt->format('Y-m-d H:i:s')
            ]
        ];
    }
}
