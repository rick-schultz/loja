<?php
defined('BASEPATH') or exit('Ação não permitida');
class Produtos extends CI_Controller
{

 public function __construct()
 {
  parent::__construct();
  if (!$this->ion_auth->logged_in()) {
   redirect('restrita/login');
  }
 }

 public function index()
 {
  $data = array(
   'titulo' => 'Produtos Cadastrados',
   'styles' => array(
    'bundles/datatables/datatables.min.css',
    'bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css',
   ),
   'scripts' => array(
    'bundles/datatables/datatables.min.js',
    'bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js',
    'bundles/jquery-ui/jquery-ui.min.js',
    'js/page/datatables.js',
   ),
   'produtos' => $this->produtos_model->get_all(),
  );

  /*   echo '<pre>';
  print_r($data['produtos']);
  exit(); */

  $this->load->view('restrita/layout/header', $data);
  $this->load->view('restrita/produtos/index');
  $this->load->view('restrita/layout/footer');
 }

 public function core($produto_id = NULL)
 {

  $produto_id = (int) $produto_id;
  if (!$produto_id) {
   //Cadastrando
  } else {
   if (!$produto = $this->core_model->get_by_id('produtos', array('produto_id' => $produto_id))) {
    $this->session->set_flashdata('erro', 'Esse produto não foi encontrado.');
    redirect('restrita/produtos');
   } else {
    //Editando
    $data = array(
     'titulo' => 'Editar Produto',
     'styles' => array(
      'jquery-upload-file/css/uploadfile.css',
     ),
     'scripts' => array(
      'jquery-upload-file/js/jquery.uploadfile.min.js',
      'jquery-upload-file/js/produtos.js',
      'jquery-upload-file/js/jquery.produtos.js',
      'mask/jquery.mask.min.js',
      'mask/custom.js',
     ),
     'produto' => $produto,
     'fotos_produto' => $this->core_model->get_all('produtos_fotos', array('foto_produto_id' => $produto_id)),
     'categorias' => $this->core_model->get_all('categorias', array('categoria_ativa' => 1)),
     'marcas' => $this->core_model->get_all('marcas', array('marca_ativa' => 1)),
    );

    /*     echo '<pre>';
    print_r($data['produto']);
    exit(); */

    $this->load->view('restrita/layout/header', $data);
    $this->load->view('restrita/produtos/core');
    $this->load->view('restrita/layout/footer');
   }
  }
 }

 public function upload()
 {
  $config['upload_path'] = './uploads/produtos/';
  $config['allowed_types'] = 'jpg|png|jpeg';
  $config['max_size'] = 2048;
  $config['max_width'] = 1000;
  $config['max_height'] = 1000;
  $config['encrypt_name'] = TRUE;
  $config['max_filename'] = 200;
  $config['file_ext_tolower'] = TRUE;

  $this->load->library('upload', $config);

  if ($this->upload->do_upload('foto_produto')) {
   $data = array(
    'uploaded_data' => $this->upload->data(),
    'mensagem' => 'Imagem enviada com sucesso',
    'foto_caminho' => $this->upload->data('file_name'),
    'erro' => 0,
   );
   //Resize image configuration
   $config['image_library'] = 'gd2';
   $config['source_image'] = './uploads/produtos/' . $this->upload->data('file_name');
   $config['new_image'] = './uploads/produtos/small' . $this->upload->data('file_name');
   $config['width'] = '300';
   $config['height'] = '300';
   //Chama a biblioteca
   $this->load->library('image_lib', $config);
   //Faz o resize
   //$this->image_lib->resize();

   if (!$this->image_lib->resize()) {
    $data['erro'] = $this->image_lib->display_errors();
   }
  } else {
   $data = array(
    'mensagem' => $this->upload->display_errors(),
    'erro' => 5,
   );
  }
  echo json_encode($data);
 }
}
