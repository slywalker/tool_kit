<?php
class JqueryUiHelper extends AppHelper {
	var $helpers = array('Html', 'Javascript');
	var $_code = array();

	function state($html, $options = array()) {
		$default = array(
			'type'=>'default',
			'corner'=>'all',
			'icon'=>'info',
		);
		$options = am($default, $options);
		$class = 'ui-state-'.$options['type'];
		unset($options['type']);
		
		if ($options['corner']) {
			$class .= ' ui-corner-'.$options['corner'];
		}
		unset($options['corner']);
		
		if ($options['icon']) {
			$html = $this->Html->tag('span', '', array('class'=>'ui-icon ui-icon-'.$options['icon'], 'style'=>'left:0.2em;margin:-8px 5px 0 0;position:absolute;top:50%;')).$html;
		}
		unset($options['icon']);
		
		$style = 'padding:0.4em 1em 0.4em 20px;position:relative;text-decoration:none;';
		if (isset($options['style'])) {
			$style = $style.$options['style'];
		}
		$options['style'] = $style;
		return $this->Html->div($class, $html, $options);
	}
	
	function accordion($contents, $options = array()) {
		$default = array(
			'id'=>'accordion',
			'class'=>null,
		);
		$options = am($default, $options);
		if (!is_array($contents) && !isset($contents[0]['title']) && !isset($contents[0]['div'])) {
			return false;
		}
		$this->_code[] = '$("#'.$options['id'].'").accordion({header:"h3"});';
		$out = '';
		foreach ($contents as $content) {
			$h3 = $this->Html->tag('h3', '<a href="#">'.$content['title'].'</a>');
			$div = $this->Html->div(null, $content['div']);
			$out .= $h3.$div;
		}
		return $this->Html->div($options['class'], $out, array('id'=>$options['id']));
	}
	
	function tabs($contents, $options = array()) {
		$default = array(
			'id'=>'tabs',
			'class'=>null,
		);
		$options = am($default, $options);
		if (!is_array($contents) && !isset($contents[0]['title']) && !isset($contents[0]['div'])) {
			return false;
		}
		$this->_code[] = '$("#'.$options['id'].'").tabs();';
		$li = array();
		$divs = '';
		$i = 1;
		foreach ($contents as $content) {
			$li[] = $this->Html->tag('h3', '<a href="#'.$options['id'].'-'.$i.'">'.$content['title'].'</a>');
			$divs .= $this->Html->div(null, $content['div'], array('id'=>$options['id'].'-'.$i));
			$i++;
		}
		$out = $this->Html->nestedList($li);
		$out .= $divs;
		return $this->Html->div($options['class'], $out, array('id'=>$options['id']));
	}
	
	function link($title, $url = null, $htmlAttributes = array(), $confirmMessage = false, $escapeTitle = true) {
		$default = array(
			'id'=>null,
			'class'=>null,
			'icon'=>'newwin',
			'state'=>'default',
			'corner'=>'all',
		);
		$htmlAttributes = am($default, $htmlAttributes);
		
		$span = '';
		if ($htmlAttributes['icon']) {
			$attr = array(
				'class'=>'ui-icon ui-icon-'.$htmlAttributes['icon'],
				'style'=>'left:0.2em;margin:-8px 5px 0 0;position:absolute;top:50%;',
			);
			$span = $this->Html->tag('span', '', $attr);
		}
		$attr = array(
			'class'=>$htmlAttributes['class'].' ui-state-'.$htmlAttributes['state'].' ui-corner-'.$htmlAttributes['corner'],
			'style'=>'padding:0.6em 1em 0.4em 20px;position:relative;text-decoration:none;',
		);
		if ($htmlAttributes['id']) {
			$attr = am($attr, array('id'=>$htmlAttributes['id']));
		}
		if ($escapeTitle) {
			$title = h($title);
		}
		$a = $this->Html->link($span.$title, $url, $attr, $confirmMessage, false);
		$this->_code[] =
<<<EOT
$(function(){
	$('p.ui-button a').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);
});
EOT;
		return $this->Html->para('ui-button', $a, array(
			'style'=>'margin:.5em 0;'));
	}

	function afterRender() {
		if ($this->_code) {
			$rs = array_unique($this->_code);
			$this->Javascript->codeBlock(
				'$(function(){'.implode(' ', $rs).'});',
				array('inline'=>false));
		}
	}
}