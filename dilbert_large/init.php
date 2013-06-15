<?php
/*
 * dilbert_large/init.php
 * Plugin for TT-RSS 1.7.9
 *
 * Replace smaller (print) version with the largest (zoom) version of the
 * Dilbert Daily Strip.
 *
 * Two feeds are known, the official one and the feedburner one:
 *   http://feed.dilbert.com/dilbert/daily_strip
 *   http://feeds.feedburner.com/DilbertDailyStrip
 *
 * In these two feeds we can have three kinds of links:
 *   http://feedproxy.google.com/~r/DilbertDailyStrip/~3/0yECkz9IKd8/
 *   http://feed.dilbert.com/~r/dilbert/daily_strip/~3/0yECkz9IKd8/
 *   http://dilbert.com/strips/comic/2013-04-24/
 *
 * Version 1.06 by amha 2013-04-25
 */
class dilbert_large extends Plugin {

  private $host;

  function about() {
    return array(
      1.06,
      'Replace smaller (print) version with the largest (zoom) version of the Dilbert Daily Strip.',
      'amha'
    );
  }

  function api_version() {
    return 2;
  }

  function init($host) {
    $this->host = $host;
    $host->add_hook($host::HOOK_RENDER_ARTICLE_CDM, $this);
    $host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
  }

  function hook_article_button($article) {
    $this->hook_render_article_cdm($article);
  }

  function hook_render_article_cdm($article) {
    if (
      strpos($article['link'], 'DilbertDailyStrip') !== FALSE
      || strpos($article['link'], 'dilbert/daily_strip') !== FALSE
      || strpos($article['link'], 'dilbert.com/strips/comic') !== FALSE
    ) {
      $article['content'] = str_replace('.strip.print.gif', '.strip.zoom.gif', $article['content']);
    }
    return $article;
  }

}
