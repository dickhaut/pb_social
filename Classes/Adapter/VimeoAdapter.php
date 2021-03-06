<?php

namespace PlusB\PbSocial\Adapter;

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('pb_social') . 'Resources/Private/Libs/';
require $extensionPath . 'vimeo/autoload.php';
use PlusB\PbSocial\Domain\Model\Feed;
use PlusB\PbSocial\Domain\Model\Item;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Ramon Mohi <rm@plusb.de>, plusB
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class VimeoAdapter extends SocialMediaAdapter
{

    const TYPE = 'vimeo';

    const VIMEO_LINK = 'https://player.vimeo.com';

    private $appKey;

    public function __construct($clientIdentifier, $clientSecret, $accessToken, $itemRepository)
    {
        parent::__construct($itemRepository);

        $this->api = new \Vimeo\Vimeo($clientIdentifier, $clientSecret, $accessToken);
    }

    public function getResultFromApi($options)
    {
        $result = array();

        $fields = array(
            // 'key' => $this->appKey,
            // 'per_page' => $options->feedRequestLimit,
            // 'part' => 'snippet'
        );

        $searchTerms = explode(',', $options->settings['vimeoChannel']);

        foreach ($searchTerms as $searchString) {
            $searchString = trim($searchString);
            $feeds = $this->itemRepository->findByTypeAndCacheIdentifier(self::TYPE, $searchString);
            if ($feeds && $feeds->count() > 0) {
                $feed = $feeds->getFirst();
                if ($options->devMod || ($feed->getDate()->getTimestamp() + $options->refreshTimeInMin * 60) < time()) {
                    try {
                        $feed->setDate(new \DateTime('now'));
                        $feed->setResult($this->getPosts($searchString, $fields, $options));
                        $this->itemRepository->updateFeed($feed);
                        $result[] = $feed;
                    } catch (\Exception $e) {
                        $this->logError("feeds can't be updated - " . $e->getMessage());
                    }
                }
                continue;
            }

            try {
                $feed = new Item(self::TYPE);
                $feed->setCacheIdentifier($searchString);
                $feed->setResult($this->getPosts($searchString, $fields, $options));
                // save to DB and return current feed
                $this->itemRepository->saveFeed($feed);
                $result[] = $feed;
            } catch (\Exception $e) {
                $this->logError('initial load for feed failed - ' . $e->getMessage());
            }
        }

        return $this->getFeedItemsFromApiRequest($result, $options);
    }

    public function getFeedItemsFromApiRequest($result, $options)
    {
        $rawFeeds = array();
        $feedItems = array();

        if (!empty($result)) {
            foreach ($result as $vimeo_feed) {
                $rawFeeds[self::TYPE . '_' . $vimeo_feed->getCacheIdentifier() . '_raw'] = $vimeo_feed->getResult();
                foreach ($vimeo_feed->getResult()->body->data as $rawFeed) {
                    $feed = new Feed(self::TYPE, $rawFeed);
                    $feed->setId($rawFeed->link);
                    $feed->setText($this->trim_text($rawFeed->name, $options->textTrimLength, true));
                    $feed->setImage($rawFeed->pictures->sizes[5]->link);
                    $feed->setLink(self::VIMEO_LINK . $rawFeed->link);
                    $d = new \DateTime($rawFeed->created_time);

                    $feed->setTimeStampTicks($d->getTimestamp());
                    $feedItems[] = $feed;
                }
            }
        }

        return array('rawFeeds' => $rawFeeds, 'feedItems' => $feedItems);
    }

    public function getPosts($searchString, $fields, $options)
    {
        if ($searchString == 'me') {
            $url = '/me/videos';
        } else {
            $url = '/channels/' . $searchString . '/videos';
        }
        $response = $this->api->request($url, array('per_page' => $options->feedRequestLimit), 'GET');
        return json_encode($response);
    }
}
