<?php
if (!function_exists('mb_trim')) {
	function mb_trim($str)
	{
		return mb_ereg_replace(
			'^[[:space:]]*([\s\S]*?)[[:space:]]*$', '\1', $str);
	}
}

class MbConvertBehavior extends ModelBehavior {

	function beforeValidate(&$model)
	{
		foreach ($model->data[$model->alias] as $key=>$data) {
			if (is_string($data)) {
				$data = preg_replace('/<link[^>]+rel="[^"]*stylesheet"[^>]*>|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i', '', $data);
				$data = mb_trim($data);
				$model->data[$model->alias][$key]
					= mb_convert_kana($data, 'a');
			}
		}
		return true;
	}
}
?>