<?php
/**
 * @file class_documents.php
 * @brief Contiene la classe documents, controller del modulo gestione di documenti
 * @author marco guidotti
 * @author abidibo
 * @version 0.1
 * @date 2014-03-06
 */
require_once('class.DocumentsCategory.php');
require_once('class.DocumentsItem.php');
/**
 * Class documents
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class documents extends Controller
{

    protected $_data_dir,
              $_data_www,
              $_view_dir;

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

        $this->_data_dir = $this->_data_dir.OS.$this->_instance_name;
        $this->_data_www = $this->_data_www."/".$this->_instance_name;

        $this->_view_dir = dirname(__FILE__).OS.'views';

        /* options
        $this->_optionsValue = array(
            'title'=>_('TItolo'),
        );
        $this->_title = htmlChars($this->setOption('title', array('value'=>$this->_optionsValue['title'])));
        $this->_options = loader::load('Options', array($this->_class_name, $this->_instance));
        $this->_optionsLabels = array(
            "title"=>_("Titolo"), 
        );
         */

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
                'documents_item',
                'documents_item_category',
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

        /*
         * delete documents items
         */
        $query = "SELECT id FROM ".DocumentsItem::$table." WHERE instance='$this->_instance'";
        $a = $this->_db->selectquery($query);
        if(sizeof($a)>0) {
            foreach($a as $b) {
                translation::deleteTranslations(DocumentsItem::$table, $b['id']);
                $query = "DELETE FROM ".DocumentsItem::$table_categories." WHERE documentsitem_id='".$b['id']."'";	
                $result = $this->_db->actionquery($query);
            }
        }

        $query = "DELETE FROM ".DocumentsItem::$table." WHERE instance='$this->_instance'";	
        $result = $this->_db->actionquery($query);

        /*
         * delete documents categories
         */
        $query = "SELECT id FROM ".DocumentsCategory::$table." WHERE instance='$this->_instance'";
        $a = $this->_db->selectquery($query);
        if(sizeof($a)>0) {
            foreach($a as $b) {
                translation::deleteTranslations(DocumentsCategory::$table, $b['id']);
            }
        }
        $query = "DELETE FROM ".DocumentsCategory::$table." WHERE instance='$this->_instance'";	
        $result = $this->_db->actionquery($query);

        /*
         * delete css files
         */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instance_name.".css");
        }

        /*
         * delete folder structure
         */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            $this->_registry->pub->deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return $result;
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
     * @brief Percorso assoluto alla cartella dei contenuti 
     * 
     * @return percorso assoluto
     */
    public function getBaseAbsPath() 
    {
        return $this->_data_dir.OS.$this->_instance_name;
    }

    /**
     * @brief Percorso relativo alla cartella dei contenuti 
     * 
     * @return percorso relativo
     */
    public function getBasePath() 
    {
        return $this->_data_www.'/'.$this->_instance_name;
    }

    /**
     * @brief Getter della proprietà instance_name 
     * 
     * @return nome dell'istanza
     */
    public function getInstanceName() 
    {
        return $this->_instance_name;
    }

    /**
     * @brief View public output
     *
     * @return vista archive
     */
    public function archive()
    {

        $this->_registry->addCss($this->_class_www."/documents_".$this->_instance_name.".css");
        $order = cleanVar($_GET, 'o', 'string', '');
        $dir = cleanVar($_GET, 'd', 'string', '');
        if(!$order or !in_array($order, array('insertion_date', 'name', 'filesize'))) $order = 'insertion_date';
        if(!$dir or $dir != 'asc') $dir = 'desc';

        if($this->userHasPerm('can_view_private')) {
            $private = true;
        }
        else {
            $private = false;
        }

        if(isset($_POST['submit_search_documents'])) {
            $name = cleanVar($_POST, 'name', 'string', '');
            $ctg = cleanVar($_POST, 'category', 'int', '');
        }
        else {
            $name = cleanVar($_REQUEST, 'name', 'string', '');
            $ctg = cleanVar($_REQUEST, 'category', 'int', '');
        }
        $search_params = array();
        $order_array = array('o' => $order, 'd' => $dir);

        $table = DocumentsItem::$table;
        $where[] = "instance='$this->_instance'";
        if($name) {
            $where[] = "name LIKE '%".$name."%'";
            $search_params[] = "name=".$name;
            $order_array['name'] = $name;
        }
        if($ctg) {
            $where[] = "id IN (SELECT documentsitem_id FROM ".DocumentsItem::$table_categories." WHERE documentscategory_id='".$ctg."')";
            $search_params[] = "category=".$ctg;
            $order_array['category'] = $ctg;
        }
        if(!$private) {
            $where[] = "private='0'";
        }

        $tot = $this->_registry->db->getNumRecords($table, implode(' AND ', $where));

        $pagination = Loader::load('PageList', array($this->_ifp, $tot, 'array'));
        $limit = array($pagination->start(), $this->_ifp);

        $documents = DocumentsItem::objects($this, array('where' => implode(' AND ', $where), 'limit' => $limit, 'order' => $order.' '.$dir));
        $view = new View($this->_view_dir, 'documents_archive_'.$this->_instance_name);
        $dict = array(
            'documents' => $documents,
            'pagination_summary' => $pagination->reassumedPrint(),
            'pagination_navigation' => $pagination->listReferenceGINO($this->_plink->aLink($this->_instance_name, 'archive', $order_array, '', array("basename"=>false))),
            'search_params' => implode('&', $search_params),
            'plink' => $this->_plink,
            'base_url' => $this->_plink->aLink($this->_instance_name, 'archive', implode('&', $search_params)),
            'order' => $order,
            'dir' => $dir,
            'form_search' => $this->formSearch(),
        );

        return $view->render($dict);
    }

    public function formSearch()
    {
        $this->_registry->addCss($this->_class_www."/documents_".$this->_instance_name.".css");

        loader::import('class', array('Form'));
        $gform = new Form('search_document', 'post', '');

        if(isset($_POST['submit_search_documents'])) {
            $name = cleanVar($_POST, 'name', 'string', '');
            $ctg = cleanVar($_POST, 'category', 'int', '');
        }
        else {
            $name = cleanVar($_REQUEST, 'name', 'string', '');
            $ctg = cleanVar($_REQUEST, 'category', 'int', '');
        }

        $form = $gform->open('', false, '');
        $form .= $gform->cselect('category', $ctg, DocumentsCategory::getForSelect($this), _('Categoria'));
        $form .= $gform->cinput('name', 'text', $name , _('Nome/Desc'), array('size' => 8));
        $form .= $gform->cinput('submit_search_documents', 'submit', _('filtra'), '', array());
        $form .= $gform->close();

        $view = new View($this->_view_dir, 'documents_form_search_'.$this->_instance_name);
        $dict = array(
            'form' => $form
        );

        return $view->render($dict);

    }

    public function download()
    {
        $id = cleanVar($_GET, 'id', 'int', '');
        $doc = new DocumentsItem($id, $this);
        if($doc->private && !$this->userHasPerm('can_view_private')) {
            error::raise404();
        }

        download($this->getBaseAbsPath().OS.$doc->filename);
    }

    /**
     * @brief Backoffice
     *
     * @return interfaccia di backoffice
     */
    public function manageDoc()
    {
        $this->requirePerm('can_admin');

        $block = cleanVar($_REQUEST, 'block', 'string', '');
        $method = 'manageDoc';

        $link_frontend = "<a href=\"".$this->_home."?evt[$this->_instance_name-$method]&block=frontend\">"._("Frontend")."</a>";
        /* $link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-$method]&block=options\">"._("Opzioni")."</a>"; */
        $link_ctg = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-$method]&block=category\">"._("Categorie")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-$method]\">"._("Documenti")."</a>";

        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $buffer = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block=='category') {
            $buffer = $this->manageDocumentsCategory();
            $sel_link = $link_ctg;
        }
        else {
            $buffer = $this->manageDocumentsItem();
        }

        // groups privileges
        $links_array = array($link_frontend, $link_ctg, $link_dft);

        $dict = array(
          'title' => _('Gestione documenti'),
          'links' => $links_array,
          'selected_link' => $sel_link,
          'content' => $buffer
        );

        $view = new view(null, 'tab');

        return $view->render($dict);
    }

    /**
     * @brief Interfaccia di amministrazione DocumentsCategory
     *
     * @return interfaccia di amministrazione
     */
    public function manageDocumentsCategory()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $buffer = $admin_table->backoffice(
            'DocumentsCategory',
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
    public function manageDocumentsItem()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $buffer = $admin_table->backoffice(
            'DocumentsItem',
            array('list_display' => array('name', 'filename', 'filesize', 'private', 'insertion_date')), // display options
            array('removeFields' => array('filesize')), // form options
            array()  // fields options
        );

        return $buffer;
    }

}
