<?php
/**
 * @file class_documents.php
 * @brief Contiene la classe documents, controller del modulo gestione di documenti
 * @author marco guidotti
 * @author abidibo
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @version 1.0.0
 */

namespace Gino\App\Documents;

use \Gino\Loader;
use \Gino\View;
use \Gino\Form;
use \Gino\Error;

/** \mainpage Caratteristiche ed output disponibili per i template e le voci di menu.    
 *
 * CARATTERISTICHE
 *
 * Modulo di gestione documenti categorizzati
 *
 *
 * OUTPUTS
 * - archivio documenti
 * - form di ricerca documenti
 */

/**
* @defgroup gino-documents
* Modulo di gestione documenti categorizzati
*
* Il modulo contiene anche dei css, javascript e file di configurazione.
*
*/

require_once('class.Category.php');
require_once('class.Document.php');

/**
 * @ingroup gino-documents
 * @brief Classe Controller per la gestione di documenti categorizzate.
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class documents extends \Gino\Controller
{

    private $_ifp;

    /**
     * @brief Costruttore
     *
     * @param $instance_id id istanza
     *
     * @return oggetto di tipo documents
     */
    public function __construct($instance_id)
    {
        parent::__construct($instance_id);

        $this->_ifp = 10;
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     *
     * @static
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo documents
     */
    public static function getClassElements() 
    {
        return array(
            "tables"=>array(
                'documents_category',
                'documents_document',
                'documents_document_category',
            ),
            "css"=>array(
                'documents.css',
            ),
            "views" => array(
                'documents_archive.php' => _('Archivio documenti'),
                'documents_form_search.php' => _('Form di ricerca documenti'),
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'documents'=> null
            ),
        );
    }

    /**
     * @brief Metodo invocato quando viene eliminata un'istanza di tipo documents
     *
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
     * 
     * @access public
     * @return bool il risultato dell'operazione
     */
    public function deleteInstance() 
    {
        $this->requirePerm('can_admin');

        /* eliminazione items */
        Document::deleteInstance($this);
        /* eliminazione categorie */
        Category::deleteInstance($this);

        /* eliminazione file css */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            @unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            @unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /* eliminazione cartelle contenuti */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return true;
    }

    /**
     * @brief Metodi pubblici disponibili per inserimento in layout a menu
     *
     * @return lista metodi pubblici
     */
    public static function outputFunctions() 
    {
        $list = array(
            "archive" => array("label"=>_("Archivio documenti"), "permissions"=>array()),
            "formSearch" => array("label"=>_("Form di ricerca documenti"), "permissions"=>array()),
        );

        return $list;
    }
    
    /**
     * @brief View public output
     * 
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function archive(\Gino\Http\Request $request)
    {

        $this->_registry->addCss($this->_class_www."/documents_".$this->_instance_name.".css");
        $order = \Gino\cleanVar($request->GET, 'o', 'string', '');
        $dir = \Gino\cleanVar($request->GET, 'd', 'string', '');
        if(!$order or !in_array($order, array('insertion_date', 'name', 'filesize'))) $order = 'insertion_date';
        if(!$dir or $dir != 'asc') $dir = 'desc';

        if($this->userHasPerm('can_view_private')) {
            $private = true;
        }
        else {
            $private = false;
        }

        if(isset($request->POST['submit_search_documents'])) {
            $name = \Gino\cleanVar($request->POST, 'name', 'string', '');
            $ctg = \Gino\cleanVar($request->POST, 'category', 'int', '');
        }
        else {
            $name = \Gino\cleanVar($request->REQUEST, 'name', 'string', '');
            $ctg = \Gino\cleanVar($request->REQUEST, 'category', 'int', '');
        }
        $search_params = array();
        $order_array = array('o' => $order, 'd' => $dir);

        $table = Document::$table;
        $where[] = "instance='$this->_instance'";
        if($name) {
            $where[] = "name LIKE '%".$name."%'";
            $search_params[] = "name=".$name;
            $order_array['name'] = $name;
        }
        if($ctg) {
            $where[] = "id IN (SELECT document_id FROM ".Document::$table_categories." WHERE category_id='".$ctg."')";
            $search_params[] = "category=".$ctg;
            $order_array['category'] = $ctg;
        }
        if(!$private) {
            $where[] = "private='0'";
        }

        $tot_records = $this->_registry->db->getNumRecords($table, implode(' AND ', $where));

        $paginator = Loader::load('Paginator', array($tot_records, $this->_ifp));
        $limit = $paginator->limitQuery();

        $documents = Document::objects($this, array('where' => implode(' AND ', $where), 'limit' => $limit, 'order' => $order.' '.$dir));
        
        $dict = array(
            'documents' => $documents,
            'pagination' => $paginator->pagination(),
            'search_params' => $request->POST,
            'router' => $this->_registry->router,
            'order' => $order,
            'dir' => $dir,
            'form_search' => $this->formSearch(),
        );

        $view = new View($this->_view_dir, 'documents_archive_'.$this->_instance_name);
        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Vista ricerca documenti
     * @description Questa vista presenta solamente un form che esegue la action sulla pagina in cui viene presentata.
     *              Per avere utilità deve essere inserita in un contesto in cui compaer anche la vista archivio.
     * 
     * @return form di ricerca
     */
    public function formSearch()
    {
        $request = $this->_registry->request;
        
        $this->_registry->addCss($this->_class_www."/documents_".$this->_instance_name.".css");

        Loader::import('class', array('\Gino\Form'));
        $gform = new Form('search_document', 'post', '');

        if(isset($_POST['submit_search_documents'])) {
            $name = \Gino\cleanVar($request->POST, 'name', 'string', '');
            $ctg = \Gino\cleanVar($request->POST, 'category', 'int', '');
        }
        else {
            $name = \Gino\cleanVar($request->REQUEST, 'name', 'string', '');
            $ctg = \Gino\cleanVar($request->REQUEST, 'category', 'int', '');
        }

        $form = $gform->open('', false, '');
        $form .= $gform->cselect('category', $ctg, Category::getForSelect($this), _('Categoria'));
        $form .= $gform->cinput('name', 'text', $name , _('Nome/Desc'), array('size' => 8));
        $form .= $gform->cinput('submit_search_documents', 'submit', _('filtra'), '', array());
        $form .= $gform->close();

        $view = new View($this->_view_dir, 'documents_form_search_'.$this->_instance_name);
        $dict = array(
            'form' => $form
        );

        return $view->render($dict);
    }

    /**
     * @brief Download di un documento
     * 
     * @throws Gino.Exception.Exception404 se l'allegato non è recuperabile
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.ResponseFile
     */
    public function download(\Gino\Http\Request $request)
    {
        $doc_id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        
        if($doc_id)
        {
        	$doc = new Document($doc_id, $this);
        	if($doc->private && !$this->userHasPerm('can_view_private')) {
            	Error::raise404();
        	}
        	
        	return \Gino\download($this->getBaseAbsPath().OS.$doc->filename);
        }
    	else {
            throw new \Gino\Exception\Exception404();
        }
    }

    /**
     * @brief Backoffice
     * 
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response backend di amministrazione del modulo
     */
    public function manageDoc(\Gino\Http\Request $request)
    {
        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->REQUEST, 'block', 'string', '');

        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Documenti'));
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=category'), _('Categorie'));
        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));

        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block=='category') {
            $backend = $this->manageCategory();
            $sel_link = $link_ctg;
        }
        else {
            $backend = $this->manageDocument();
        }
        
    	if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $dict = array(
            'title' => _('Gestione documenti'),
            'links' => array($link_frontend, $link_ctg, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View(null, 'tab');
        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione DocumentsCategory
     *
     * @return interfaccia di amministrazione
     */
    public function manageCategory()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $buffer = $admin_table->backoffice(
            'Category',
            array(), // display options
            array(), // form options
            array()  // fields options
        );

        return $buffer;
    }

    /**
     * @brief Interfaccia di amministrazione DocumentsItem
     *
     * @return interfaccia di amministrazione
     */
    public function manageDocument()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $buffer = $admin_table->backoffice(
            'Document',
            array('list_display' => array('name', 'filename', 'filesize', 'private', 'insertion_date')), // display options
            array('removeFields' => array('filesize')), // form options
            array()  // fields options
        );

        return $buffer;
    }

}
