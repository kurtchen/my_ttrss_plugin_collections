<?php
class Evernote extends Plugin {
	private $link;
	private $host;

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
	}

	function about() {
		return array(1.0,
			"Save article on Evernote",
			"m.s");
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/evernote.js");
	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<img src=\"".theme_image($this->link, 'plugins/evernote/evernote.png')."\"
			class='tagsPic' style=\"cursor : pointer\"
			onclick=\"saveArticleOnEvernote($article_id)\"
			title='".__('Save article on Evernote')."'>";

		return $rv;
	}

	function getInfo() {
		$id = db_escape_string($_REQUEST['id']);

		$result = db_query($this->link, "SELECT title, link
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

		if (db_num_rows($result) != 0) {
			$title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
				100, '...');
			$article_link = db_fetch_result($result, 0, 'link');
		}

		print json_encode(array("title" => $title, "link" => $article_link,
				"id" => $id));
	}


}
?>
