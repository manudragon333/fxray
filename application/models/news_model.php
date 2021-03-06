<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of login
 *
 * @author RAJU
 */
class News_model extends MY_Model {

//put your code here
    public function __construct() {
        parent::__construct();
    }

    function news_view($id = 0) {
        $ref_id = $this->common_model->getReferenceID('news');
        $sql = 'SELECT n.*,a.url,a.original_file_name,a.db_file_name,a.id as att_id FROM news n
				LEFT JOIN attachments a on a.global_id = n.id and a.reference_id = ' . $ref_id . ' 
				where n.id=' . $id;
        return $this->getDBResult($sql, 'object');
    }

    function save_news($post = array(), $files = array()) {
        if ($post['id']) {
            $post['n_id'] = $n_id = $this->saveRecord(conversion($post, 'news_lib'), 'news', array('id' => $post['id']));
        } else {
            $post['n_id'] = $n_id = $this->saveRecord(conversion($post, 'news_lib'), 'news');
        }
        if (isset($post['original_file_name']) && !empty($post['original_file_name']) && $post['n_id'] != 0) {
            $ref_id = $this->common_model->getReferenceID('news');
            if ($post['id'] != 0) {
                $n_id = $post['id'];
                $this->common_model->delete_attachments($post['id'], $ref_id);
            }
            $db_file_name = ((isset($post['db_file_name']))?$post['db_file_name']:'');
            $original_file_name = $post['original_file_name'];
            $url = 'uploads/' . ((isset($post['db_file_name']))?$post['db_file_name']:'');
            $global_id = $n_id;
            $reference_id = $ref_id;
            $_POST['attachments_id'] = $this->fileupload_model->save_attachment($db_file_name, $original_file_name, $global_id, $reference_id);
        }
        return $post['n_id'];
    }

    function getnewsdata() {
        $sql = 'select n.id,n.heading,n.description,DATE_FORMAT(n.date_added, "%m/%d/%y") as date_added,
		 		nc.name,CASE WHEN n.status =1 THEN "Publish" ELSE "Unpublish" END as status_name 
				from news n
				left join news_categories nc on n.news_categories_id=nc.id';
        $data_flds = array('heading', 'date_added', 'status_name', "<a class='btn edit_ecur ' href='" . site_url() . "adminnews/news_view/{%id%}'id='{%id%}'><span class='inner-btn'><span class='label'>Edit</span></span></a>");
        return $this->users_model->display_grid($_POST, $sql, $data_flds);
    }

    function getnews($where_id = '', $limit = '') {
        $ref_id = $this->common_model->getReferenceID('news');
        $sql = 'select n.id,n.heading,n.description,DATE_FORMAT(n.date_added, "%b %d %Y") as date_added,
		 		nc.name as cat_name,a.url as attachment
				from news n
				LEFT JOIN news_categories nc on n.news_categories_id=nc.id
				LEFT JOIN attachments a on a.global_id = n.id and a.reference_id = ' . $ref_id . ' 
				where n.status=1 ' . $where_id . ' order by n.last_modified desc ' . $limit;
        $data = $this->getDBResult($sql, 'object');

        return $data;
    }

}