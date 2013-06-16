<?php
class Af_Search_Title extends Plugin {
    private $host;

    function about() {
        return array(1.0,
            'Add quick link to search title',
            'Kurt Chen');
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

        if (strpos($article['link'], 'quotationspage.com/') !== FALSE) {
            // This string will be saved in the `plugin_data` field in the
            // database for the corresponding item after applying the filter.
            // By checking for the existence of this value, we make sure the
            // filter isn't applied everytime the items are loaded.
            $plugin_string = 'af_search_title,' . $owner_uid . ':';

            if (strpos($article['plugin_data'], $plugin_string) === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML($article['content']);

                if ($doc) {

                    $body = $doc->getElementsByTagName('body')->item(0);

                    // If we fail to find this element, something has gone
                    // awry. Then do nothing and just return $article as it
                    // was. Else, write the new content to the database.
                    if ($body) {
                        $body->appendChild($doc->createElement('br'));

                        $link = $doc->createElement('a');
                        $link->setAttribute('href', 'http://www.google.com/search?q=' . $article['title']);
                        $link->appendChild($doc->createTextNode('Google'));
                        $body->appendChild($link);

                        $body->appendChild($doc->createElement('br'));

                        $link = $doc->createElement('a');
                        $link->setAttribute('href', 'http://en.wikipedia.org/wiki/' . $article['title']);
                        $link->appendChild($doc->createTextNode('Wikipedia'));
                        $body->appendChild($link);

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

