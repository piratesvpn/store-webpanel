<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tools extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('categories_model');
		$this->load->model('items_model');
    }
	
	public function impex(){
		$data['page'] = 'tools';
		
		$this->load->view('parts/header',$data);
		$this->load->view('pages/tools/impex',$data);
		$this->load->view('parts/footer');
	}

	public function update(){
		$data['page'] = 'tools';
		
		$this->load->view('parts/header',$data);
		$this->load->view('pages/tools/update',$data);
		$this->load->view('parts/footer');
	}

	public function import()
	{
		$config['upload_path'] = './uploads/';      
		$config['allowed_types'] = 'json';
		
		$this->load->library('upload', $config);
		
		if ($this->upload->do_upload('importFile'))
		{
			$json = json_decode(file_get_contents($this->upload->data()['file_path'].$this->upload->data()['file_name']));
			
			$this->items_model->delete_items_by_type($json->type);
			
			foreach ($json->categories as $category)
			{
				$category_id = $this->categories_model->add_category($category->display_name, 
																		$category->description, 
																		$category->require_plugin, 
																		$category->web_description, 
																		$category->web_color);
				foreach ($category->items as $item)
				{
					$this->items_model->add_item($item->name,
													$item->display_name,
													$item->description,
													$item->web_description,
													$item->type,
													$item->loadout_slot,
													$item->price,
													json_encode($item->attrs, JSON_UNESCAPED_SLASHES),
													$item->is_buyable,
													$item->is_tradeable,
													$item->is_refundable,
													$category_id);
				}
			}
			
			redirect('/items', 'refresh');
		}
		else
		{
			print_r($this->upload->display_errors());
		}		
	}
	
	public function export()
	{
		$post = $this->input->post();
		$categories = $this->categories_model->get_categories_by_type($post['itemType']);
		
		foreach ($categories as &$category)
		{
			unset($category['id']);
			foreach ($category['items'] as &$item)
			{
				unset($item['id']);
				unset($item['category_id']);
				$item['attrs'] = json_decode($item['attrs']);
			}
		}
		$this->output
			->set_content_type('application/octet-stream')
			->set_header("Content-Disposition: attachment; filename=".$post['itemType'].".json")
			->set_output(json_encode(array('type' => $post['itemType'], 'categories' => $categories), JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
	}
}

/* End of file tools.php */
/* Location: ./application/controllers/tools.php */