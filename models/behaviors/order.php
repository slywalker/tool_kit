<?php
class OrderBehavior extends ModelBehavior {

	function setup(&$model, $config=array())
	{
		if ($model->hasField('order')) {
			$model->order = array(
				$model->alias.'.order'=>'ASC',
				$model->alias.'.'.$model->primaryKey=>'DESC',
			);
		}
	}
	
	function afterSave(&$model, $created)
	{
		if ($model->hasField('order') && $created) {
			$model->recursive = false;
			$options = array('fields'=>array('id'));
			if ($model->alias === 'ProfilePhoto') {
				$options = $this->_ifProfilePhoto($model, $options, $model->getInsertID());
			}
			$results = $model->find('all', $options);
			foreach ($results as $key=>$result) {
				$model->id = $result[$model->alias]['id'];
				if (!$model->saveField('order', ($key + 1))) {
					return false;
				}
			}
		}
		return true;
	}

	/*
	*  ProfilePhotoのときの処理
	*/
	function _ifProfilePhoto(&$model, $options, $id)
	{
		$profilePhoto
			= $model->read('profile_profile_id', $id);
		$profile_profile_id
			= $profilePhoto['ProfilePhoto']['profile_profile_id'];
		$options = Set::merge($options, array(
			'conditions'=>array(
				'ProfilePhoto.profile_profile_id'=>$profile_profile_id,
			),
		));
		return $options;
	}
	
	function updateOrder(&$model, $method, $id)
	{
		$methods = array(
			'up'=>array('inequality'=>' <', 'order'=>'DESC'),
			'down'=>array('inequality'=>' >', 'order'=>'ASC'),
		);
		if (!array_key_exists($method, $methods)) {
			trigger_error('OrderBehavior Error: The method '.$method.' does not exist.', E_USER_WARNING);
			return false;
		}
		$order = $model->field('order', array('id'=>$id));
		if (!$order) {
			trigger_error('OrderBehavior Error: The field order does not exist.', E_USER_WARNING);
			return false;
		}
		$options = array(
			'conditions'=>array(
				'order'.$methods[$method]['inequality']=>$order,
			),
			'fields'=>array('id', 'order'),
			'order'=>array(
				'order'=>$methods[$method]['order']
			),
		);
		if ($model->alias === 'ProfilePhoto') {
			$options = $this->_ifProfilePhoto($model, $options, $id);
		}
		$model->recursive = -1;
		$result = $model->find('first', $options);
		if (!$result) {
			trigger_error('OrderBehavior Error: The result does not exist.', E_USER_WARNING);
			return false;
		}
		$model->id = $result[$model->alias]['id'];
		if (!$model->saveField('order', $order)) {
			return false;
		}
		$model->id = $id;
		if (!$model->saveField('order', $result[$model->alias]['order'])) {
			return false;
		}
		
		return true;
	}
}
?>