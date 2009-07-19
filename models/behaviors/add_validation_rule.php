<?php
class AddValidationRuleBehavior extends ModelBehavior {

	function checkCompare(&$model, $data, $suffix) {
		$field = key($data);
		$value = current($data);
		if (isset($model->data[$model->alias][$field.$suffix])) {
			return $value === $model->data[$model->alias][$field.$suffix];
		}
		return true;
	}
	
	function alphaNumeric(&$model, $data) {
		$value = current($data);
		return preg_match('/^[a-z\d]*$/i', $value);
	}

	function maxMbLength(&$model, $data, $length) {
		$value = current($data);
		return mb_strlen($value) <= $length;
	}
}
?>