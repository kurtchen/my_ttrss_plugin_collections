<?php
class Af_Slashdot extends Plugin {
    private $host;

    function about() {
        return array(1.1,
            'Correctly sized iframe insertions in Slashdot posts',
            'dandersson',
            false,
            "http://tt-rss.org/forum/viewtopic.php?f=22&t=1836");
    }

    function init($host) {
        // Boilerplate to register hooks.
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }
   
   function api_version() {
      return 2;
   }

    function hook_article_filter($article) {
        $owner_uid = $article['owner_uid'];

        // Only match Slashdot posts. Select by item link target.
        if (strpos($article['link'], 'slashdot.org/') !== FALSE) {
            // This string will be saved in the `plugin_data` field in the
            // database for the corresponding item after applying the filter.
            // By checking for the existence of this value, we make sure the
            // filter isn't applied everytime the items are loaded.
            $plugin_string = 'af_slashdot,' . $owner_uid . ':';

            if (strpos($article['plugin_data'], $plugin_string) === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML($article['content']);

                if ($doc) {
                    $ps = $doc->getElementsByTagName('p');
                    // Only keep first paragraph (that's where the summary is,
                    // the rest is just cruft).
                    while ($ps->item(1)) {
                        $ps->item(1)->parentNode->removeChild($ps->item(1));
                    }

                    // Keep the iframe with the comments. Set width and height
                    // since style attributes are stripped.
                    $iframe = $doc->getElementsByTagName('iframe')->item(0);
                    $iframe->setAttribute('width', '100%');
                    $iframe->setAttribute('height', '324');

                    // Remove G+/FB/Twitter/etc. images and unnecessary links.
                    // Might be easier to just fetch the summary and comments
                    // and build a completely new DOMObject to discard
                    // everything else.
                    $search = new DomXPath($doc);
                    $tags = $search->query('//div|//img|//a|//br');
                    foreach ($tags as $tag) {
                        $tag->parentNode->removeChild($tag);
                    }

                    $body = $doc->getElementsByTagName('body')->item(0);

                    // If we fail to find this element, something has gone
                    // awry. Then do nothing and just return $article as it
                    // was. Else, write the new content to the database.
                    if ($body) {
                            $article['content'] = $doc->saveXML($body, LIBXML_NOEMPTYTAG);
                            $article['plugin_data'] = $plugin_string . $article['plugin_data'];
                    }
                }
            } else if (isset($article['stored']['content'])) {
                $article['content'] = $article['stored']['content'];
            }
        }
        return $article;
    }
}
?>

