<?php
uses('Xml');
class Rss extends AppModel {
	var $name = 'Rss';
	var $useTable = false;
	var $cacheDuration = '+1 hours';

	function find($method, $options = array())
	{
		$default = array(
			'url'=>null,
			'limit'=>null,
			'offset'=>0,
		);
		$options = am($default, $options);
		if (!$options['url']) {
			return $this->_errorRss($method);
		}
		
		$results = array();
		if ($this->cacheDuration) {
			$key = Security::hash($options['url']);
			Cache::set(array('duration'=>$this->cacheDuration));
			$results = Cache::read('rss_cache'.$key);
		}
		if (!$results) {
			$data = @file_get_contents($options['url']);
			if (!$data) {
				return $this->_errorRss($method);
			}
			if (!$results = $this->_parse($data)) {
				return $this->_errorRss($method);
			}
			if ($this->cacheDuration) {
				Cache::write('rss_cache'.$key, $results);
			}
		}
		
		if ('all' === $method) {
			$results['items'] = array_slice(
				$results['items'],
				$options['offset'],
				$options['limit']);
			return $results;
		}
		elseif ('count' === $method) {
			return count($results['items']);
		}
		else {
			return array();
		}
	}
	
	function _errorRss($method)
	{
		if ('all' === $method) {
			return array();
		}
		elseif ('count' === $method) {
			return 0;
		}
		return false;
	}
	
	function _parse($data)
	{
		$xml = new Xml($data);
		$xml = Set::reverse($xml);
		// RSS1.0
		if (isset($xml['RDF'])) {
			$title = $xml['RDF']['Channel']['title'];
			$link = $xml['RDF']['Channel']['link'];
			$items = array();
			foreach ($xml['RDF']['Item'] as $key=>$item) {
				$items[$key]['pubDate'] =
					date('Y-m-d H:i:s', strtotime($item['date']));
				$items[$key]['title'] = $item['title'];
				$items[$key]['link'] = $item['link'];
				if (isset($item['encoded'])) {
					$items[$key]['description'] = $item['encoded'];
				} else {
					$items[$key]['description'] = $item['description'];
				}
			}
		}
		// RSS2.0
		elseif (isset($xml['Rss'])) {
			$title = $xml['Rss']['Channel']['title'];
			$link = $xml['Rss']['Channel']['link'];
			$items = array();
			foreach ($xml['Rss']['Channel']['Item'] as $key=>$item) {
				$items[$key]['pubDate'] =
					date('Y-m-d H:i:s', strtotime($item['pubDate']));
				$items[$key]['title'] = $item['title'];
				$items[$key]['link'] = $item['link'];
				$items[$key]['description'] = $item['description'];
			}
		}
		// Atom
		elseif (isset($xml['Feed'])) {
			$title = $xml['Feed']['title'];
			$link = $xml['Feed']['Link'][0]['href'];
			$items = array();
			foreach ($xml['Feed']['Entry'] as $key=>$item) {
				$items[$key]['pubDate'] =
					date('Y-m-d H:i:s', strtotime($item['issued']));
				$items[$key]['title'] = $item['title'];
				$items[$key]['link'] = $item['Link']['href'];
				$items[$key]['description'] = $item['content']['value'];
			}
		}
		else {
			return false;
		}
		return compact('title', 'link', 'items');
	}
}
?>