<?php
/**
 * ForeignKeyBehavior
 * 
 * How to use.
 * Ex.
 * 	class AppModel extends Model {
 * 		var $actsAs = array('ForeignKey');
 * 
 * 		function readForeignKeyValue()
 * 		{
 * 			return Configure::read('User.id');
 * 		}
 * 	}
 *
 * @package default
 * @author Yasuo Harada
 */
class ForeignKeyBehavior extends ModelBehavior {
	var $foreignKey = 'user_id';
	var $modelName = 'User';
	var $callBack = 'readForeignKeyValue';

	function setup(&$model, $config=array())
	{
		if (isset($config['foreignKey'])) {
			$this->foreignKey = $config['foreignKey'];
			$this->modelName = str_replace('Id', '', Inflector::camelize($config['foreignKey']));
		}
		if (isset($config['modelName'])) {
			$this->modelName = $config['modelName'];
		}
		if (isset($config['callBack'])) {
			$this->callBack = $config['callBack'];
		}
	}

	function beforeFind(&$model, $query)
	{
		if ($model->name === $this->modelName) {
			$id = $model->{$this->callBack}();
			if ($id) {
				$conditions = array(
					$model->name.'.id'=>$id,
				);
				$query['conditions'] = Set::merge($query['conditions'], $conditions);
			}
		}
		
		elseif ($model->hasField($this->foreignKey)) {
			$value = $model->{$this->callBack}();
			if ($value) {
				$conditions = array(
					$model->alias.'.'.$this->foreignKey=>$value,
				);
				$query['conditions'] = Set::merge($query['conditions'], $conditions);
			} else {
				trigger_error(__("ForeignKeyBehavior: Can't set at find foreign key [{$this->foreignKey}] in {$model->alias}.", true), E_USER_ERROR);
			}
		}
		return $query;
	}
	
	function beforeValidate(&$model)
	{
		if ($model->hasField($this->foreignKey)) {
			$value = $model->{$this->callBack}();
			if ($value) {
				$model->data[$model->alias][$this->foreignKey] = $value;
			} else {
				trigger_error(__("ForeignKeyBehavior: Can't set at save foreign key [{$this->foreignKey}] in {$model->alias}.", true), E_USER_ERROR);
			}
		}
	}
}
?>