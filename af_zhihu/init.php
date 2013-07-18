<?php
class Af_Zhihu extends Plugin {
    private $host;

    function about() {
        return array(1.0,
            'Full article for Zhihu',
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

        if (strpos($article['link'], 'zhihu.com/') !== FALSE) {
            // This string will be saved in the `plugin_data` field in the
            // database for the corresponding item after applying the filter.
            // By checking for the existence of this value, we make sure the
            // filter isn't applied everytime the items are loaded.
            $plugin_string = 'af_zhihu,' . $owner_uid . ':';

            if (strpos($article['plugin_data'], $plugin_string) === FALSE) {
                $article['content'] = preg_replace(array('/<t[0-9]+>/','/<\/t[0-9]+>/'),'',$article['content']);
                $article['plugin_data'] = $plugin_string . $article['plugin_data'];
            } else if (isset($article['stored']['content'])) {
                $article['content'] = $article['stored']['content'];
            }
        }
        return $article;
    }
}
?>

