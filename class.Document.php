<?php
/**
 * @file class.Document.php
 * @brief Class Document
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @version 1.0.0
 */

namespace Gino\App\Documents;

use \Gino\BooleanField;
use \Gino\DatetimeField;
use \Gino\FileField;
use \Gino\ManyToManyField;

/**
 * @ingroup gino-documents
 * Classe tipo @ref Model che rappresenta una categoria di documenti.
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Document extends \Gino\Model
{
    public static $table = 'documents_document';
    public static $table_categories = 'documents_document_category';
    private static $_extension_file = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt', 'txt', 'zip', 'rar', 'png', 'jpg');

    /**
     * @brief Costruttore
     * @param int $id id del documento
     * @param documents $instance istanza del controller
     * @return istanza di @ref Document
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'filename' => array(_('File'), _('Estensioni permesse: ').implode(',', self::$_extension_file)),
            'filesize' => _('Dimensioni'),
            'description' => _('Descrizione'),
            'private' => array(_('Privato'), _('I documenti privati saranno visualizzabili solamente da chi ha il relativo permesso')),
            'insertion_date' => _('Data inserimento'),
            'categories' => _('Categorie'),
        );

        parent::__construct($id);

        $this->_model_label = _('Documento');
    }

    /**
     * @brief Casting a stringa
     *
     * @return rappresentazione a stringa dell'oggetto
     */
    function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @brief Definizione della struttura del modello
     *
     * @param $id id dell'istanza
     *
     * @return struttura del modello
     */
    public function structure($id)
    {
        $structure = parent::structure($id);

        $structure['private'] = new BooleanField(array(
            'name'=>'private', 
            'model'=>$this,
            'enum'=>array(1 => _('si'), 0 => _('no')),
        ));

        $structure['insertion_date'] = new DatetimeField(array(
            'name'=>'insertion_date',
            'model'=>$this,
            'auto_now'=>false,
            'auto_now_add'=>true,
        ));

        $base_path = $this->_controller->getBaseAbsPath('attached');

        $structure['filename'] = new FileField(array(
            'name'=>'filename',
            'model'=>$this,
            'extensions'=>self::$_extension_file,
            'path'=>$base_path,
            'required'=>true,
            'check_type'=>false,
            'filesize_field' => 'filesize'
        ));

        $structure['categories'] = new ManyToManyField(array(
            'name' => 'categories',
            'model' => $this,
            'm2m' => '\Gino\App\Documents\Category',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_categories,
            'add_related' => true,
            'add_related_url' => $this->_home.'?evt['.$this->_controller->getInstanceName().'-manageDoc]&block=category&insert=1',
        ));


        return $structure;
    }

    /**
     * @brief Url per il download del documento
     * @return url
     */
    public function downloadUrl()
    {
        return $this->_controller->getInstanceName().'/download/'.$this->id;
    }
}

