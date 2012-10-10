<?php


/**
 * @property \ModelImportProducts $model_import_products
 */
class ControllerModuleImport extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('module/import');
		$this->document->setTitle($this->language->get('heading_title'));

		/** @var \ModelImportProducts $imports */
		$imports = $this->load->model('import/products');

		$this->data['changelog'] = array();
		$this->data['success'] = $this->data['error'] = '';
		if (isset($this->request->get['do'])
			&& isset($this->request->get['import'])
			&& ($this->request->get['do'] == 'import') && $this->validate()) {

			$file = $this->session->data['token'] . '.' . $this->request->get['import'];
			$imports->setProgressPublisher(new ProgressPublisher($file));

			if ($imports->import($this->request->get['import'])) {
				$this->data['success'] = $this->language->get('text_success');

			} else {
				$this->data['error'] = $this->language->get('text_fail');
			}

			$this->data['changelog'] = $imports->changelog();
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['action_load'] = $this->language->get('action_load');

		$this->data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->link('common/home'),
				'separator' => false
			),
			array(
				'text' => 'Zboží',
				'href' => $this->link('catalog/product'),
				'separator' => ' :: '
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->link('module/import'),
				'separator' => ' :: '
			)
		);

		$this->data['imports'] = array();
		foreach ($imports->getAvailable() as $class => $xmlFeed) {
			$this->data['imports'][] = array(
				'title' => implode(', ', $xmlFeed->getManufacturers()),
				'importFile' => $this->url->getSsl() .  'model/import/progress/' . $this->session->data['token']  .  '.' . $class .  '.json',
				'action' => $this->link('module/import', array('do' => 'import', 'import' => $class))
			);
		}

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH'])
			&& $this->request->server['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {

			echo json_encode($this->data);
			exit;
		}

		$this->template = 'module/import.tpl';
		$this->children = array('common/header', 'common/footer');

		$this->response->setOutput($this->render());
	}



	/**
	 * @return bool
	 */
	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'module/import')) {
			$this->data['error'] = $this->language->get('error_permission');
		}

		return !isset($this->data['error']) || !$this->data['error'];
	}

}
