<?php

class ControllerExtensionModuleVGallery extends Controller{
    private $error = array();
    public $params = NULL;

    public function index(){
        //Add JS/CSS
//        $this->document->addStyle('view/stylesheet/datatables/datatables.min.css');
//        $this->document->addScript('view/javascript/datatables/datatables.min.js');
        $this->document->addScript('view/javascript/bootbox/bootbox.all.min.js');

        $this->load->language('extension/module/v_gallery');
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->document->setTitle($this->language->get('heading_title'));
        $data['language'] = $this->session->data['language'];
        $current_language_id = $this->model_localisation_language->getLanguageByCode($data['language'])['language_id'];
       $data['current_language'] = $current_language_id;

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if(isset($this->request->post['add_new_gallery'])){
                $query = "INSERT INTO `" . DB_PREFIX . "v_gallery` (`name`, `description`, `language_id`) VALUES ('" . $this->request->post['gallery_name'][$current_language_id] . "', '" . $this->request->post['description'][$current_language_id] . "', '" . $current_language_id . "')";
                $this->db->query($query);
                $lastId = $this->db->getLastId();
                foreach ($data['languages'] as $language) {
                    $id = $language['language_id'];
                    $query = "INSERT INTO `" . DB_PREFIX . "v_gallery_lang` (`name`, `v_gallery_id`, `description`, `language_id`) VALUES ('" . $this->request->post['gallery_name'][$id] . "','" . $lastId . "', '" . $this->request->post['description'][$id] . "', '" . $id . "')";
                    $this->db->query($query);
                }
                    $this->session->data['success'] = $this->language->get('text_success');
                    $this->response->redirect($this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));

            }

            if(isset($this->request->post['add_new_gallery_item'])){
                $query = "INSERT INTO `".DB_PREFIX."v_gallery_items` (`v_gallery_id`, `title`, `description`, `src`, `language_id`) 
                VALUES ('".$this->request->post['v_gallery_id']."', '".$this->request->post['title'][$current_language_id]."', '".$this->request->post['description'][$current_language_id]."', '".$this->request->post['src']."', '".$current_language_id."')";
                $this->db->query($query);
                $lastId = $this->db->getLastId();

                foreach ($data['languages'] as $language) {
                    $id = $language['language_id'];
                    $query = "INSERT INTO `".DB_PREFIX."v_gallery_items_lang` (`v_gallery_item_id`, `title`, `description`, `language_id`) 
                VALUES ('".$lastId."', '".$this->request->post['title'][$id]."', '".$this->request->post['description'][$id]."', '".$id."')";
                    $this->db->query($query);
                }

                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'] . '&type=module&v_gallery_id='.$this->request->post['v_gallery_id'], true));

            }

            if(isset($this->request->post['update_gallery'])){
                $query = "UPDATE `" .DB_PREFIX."v_gallery` SET `name`='".$this->request->post['gallery_name'][$current_language_id]."', `description`='".$this->request->post['description'][$current_language_id]."', `language_id`='".$current_language_id."' WHERE `v_gallery_id`='".$this->request->post['gallery_id']."'";
//                print_r($query).die();
                $this->db->query($query);
//                $lastId = $this->db->getLastId();
                foreach ($data['languages'] as $language) {
                    $id = $language['language_id'];
                    $query = "UPDATE `" . DB_PREFIX . "v_gallery_lang` SET `name`='".$this->request->post['gallery_name'][$id]."', `description`='".$this->request->post['description'][$id]."' WHERE `v_gallery_id`='".$this->request->post['gallery_id']."' AND `language_id`='".$language['language_id']."'";
                    $this->db->query($query);
                }
                    $this->session->data['success'] = $this->language->get('text_success');
                    $this->response->redirect($this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));


            }

            if(isset($this->request->post['update_gallery_item'])){
                $query = "UPDATE `" .DB_PREFIX."v_gallery_items` SET `title`='".$this->request->post['title'][$current_language_id]."', `description`='".$this->request->post['description'][$current_language_id]."', `src`='".$this->request->post['src']."', `language_id`='".$current_language_id."' WHERE `v_gallery_item_id`='".$this->request->post['v_gallery_item_id']."'";
                $this->db->query($query);
                foreach ($data['languages'] as $language) {
                    $id = $language['language_id'];
                    $query_lang = "UPDATE `" . DB_PREFIX . "v_gallery_items_lang` SET `title`='".$this->request->post['title'][$id]."', `description`='".$this->request->post['description'][$id]."' WHERE `v_gallery_item_id`='".$this->request->post['v_gallery_item_id']."' AND `language_id`='".$language['language_id']."'";
                    $this->db->query($query_lang);
                }
                //here
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'] . '&type=module&v_gallery_id='.$this->request->post['v_gallery_id'], true));
            }

        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->session->data['user_token'];

//        print_r( $this->session->data['language']); die();
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['warning'])) {
            $data['error'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        } else {
            $data['error'] = '';
        }
        $data['url'] = $this->url->link('extension/module/v_gallery', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['galleries'] = $this->galleries();

        if(isset($this->request->get['delete_gallery_id'])){
            $this->deleteIt($this->request->get['delete_gallery_id'], 'gallery');
            $this->response->redirect($data['url']);
        }

        if(isset($this->request->get['delete_gallery_item_id'])){
            $gallery_id = $this->request->get['gallery_id'];
            $this->deleteIt($this->request->get['delete_gallery_item_id'], 'gallery_item');
            $this->response->redirect($data['url'].'&v_gallery_id='.$gallery_id);
        }

        if(isset($this->request->get['v_gallery_id'])){
            $gallery = (object) $this->getGallery($this->request->get['v_gallery_id']);
            $gallery_items = $this->getGalleryItems($this->request->get['v_gallery_id']);

            $data['gallery'] = $gallery;
            $data['gallery_items'] = $gallery_items;
            $data['heading_title'] = $gallery->name;
        }

        $this->response->setOutput($this->load->view('extension/module/v_gallery', $data));

    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/v_gallery')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "v_gallery` (
		  `v_gallery_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(111) NOT NULL,
		  `description` text COLLATE utf8_bin,
		  `date_added` datetime,
		  `date_updt` datetime,
		  `status` tinyint(1) DEFAULT 1,
		  `language_id` int(5) DEFAULT 1,
		  PRIMARY KEY (`v_gallery_id`)
		)");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "v_gallery_lang` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
		  `v_gallery_id` int(11) NOT NULL,
		  `name` varchar(111) NOT NULL,
		  `description` text COLLATE utf8_bin,
		  `language_id` int(5) DEFAULT 1,
		  PRIMARY KEY (`id`)
		)");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "v_gallery_items` (
		  `v_gallery_item_id` int(11) NOT NULL AUTO_INCREMENT,
		  `v_gallery_id` int(11) NOT NULL,
		  `title` varchar(111) NOT NULL,
		  `description` text COLLATE utf8_bin,
		  `src` text COLLATE utf8_bin,
		  `language_id` int(5) DEFAULT 1,
		  PRIMARY KEY (`v_gallery_item_id`)
		)");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "v_gallery_items_lang` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
		  `v_gallery_item_id` int(11) NOT NULL,
		  `title` varchar(111) NOT NULL,
		  `description` text COLLATE utf8_bin,
		  `language_id` int(5) DEFAULT 1,
		  PRIMARY KEY (`id`)
		)");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX ."v_gallery_items`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX ."v_gallery`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX ."v_gallery_lang`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX ."v_gallery_items_lang`");
    }

    public function galleries(){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery` ORDER BY `v_gallery_id` DESC");
        array_walk($query->rows, function (&$value){
            $v_gallery_id = $value['v_gallery_id'];
            $galleryLang = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery_lang` WHERE `v_gallery_id`=$v_gallery_id")->rows;
            $value['lang'] = $galleryLang;
        });
//        print_r($query->rows).die();
        return $query->rows;
    }

    public function getGallery($id){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery` WHERE `v_gallery_id`=$id");
        return $query->row;
    }

    public function getGalleryItems($id){
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery_items` WHERE `v_gallery_id`=$id ORDER BY `v_gallery_item_id` DESC");
        $items = [];
        foreach ($query->rows as $row){
            $row['src'] = $this->getYoutubeEmbedUrl($row['src']);
            $items[] = $row;
        }

        array_walk($items, function (&$value){
            $v_gallery_item_id = $value['v_gallery_item_id'];
            $galleryItemLang = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery_items_lang` WHERE `v_gallery_item_id`=".$v_gallery_item_id)->rows;
            $value['lang'] = $galleryItemLang;
        });
//        print_r($items).die();
        return $items;
    }

    public function getYoutubeEmbedUrl($url)
    {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';
        $youtube_id = "";
        if (preg_match($longUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }
        return 'https://www.youtube.com/embed/' . $youtube_id ;
    }

    public function deleteIt($id, $type){
        if($type=='gallery'){
            $items = $this->db->query("SELECT * FROM `" . DB_PREFIX . "v_gallery_items` WHERE `v_gallery_id`=$id");
            foreach ($items->rows as $item){
                $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery_items_lang` WHERE `v_gallery_item_id`=".$item['v_gallery_item_id']);
            }
            $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery_items` WHERE `v_gallery_id`=$id");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery_lang` WHERE `v_gallery_id`=$id");
            $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery` WHERE `v_gallery_id`=$id");
            if($query){
                return true;
            }else{
                return false;
            }
        } else {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery_items_lang` WHERE `v_gallery_item_id`=$id");
            $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "v_gallery_items` WHERE `v_gallery_item_id`=$id");
            if($query){
                return true;
            }else{
                return false;
            }
        }
    }
}