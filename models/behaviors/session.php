<?php
App::import('Component', 'Session');
class SessionBehavior extends ModelBehavior {
	public function setup(&$model, $config=array()) {
		$model->Session = new SessionComponent;
	}
}
?>