<?php
/**
 * @file class.DocumentsItem.php
 * @brief Class DocumentsItem
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @version 0.1
 * @date 2014-03-06
 */
class DocumentsItem extends Model
{
    private $_controller;
    public static $table = 'documents_item';
    public static $table_categories = 'documents_item_category';
    private static $_extension_file = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt', 'txt', 'zip', 'rar', 'png', 'jpg');

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

        $this->_model_label = _('Documenti');
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

    public function getController()
    {
        return $this->_controller;
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

        $structure['private'] = new booleanField(array(
            'name'=>'private', 
            'model'=>$this,
            'enum'=>array(1 => _('si'), 0 => _('no')),
        ));

        $structure['insertion_date'] = new datetimeField(array(
            'name'=>'insertion_date',
            'model'=>$this,
            'auto_now'=>false,
            'auto_now_add'=>true,
        ));

        $base_path = $this->_controller->getBaseAbsPath('attached');

        $structure['filename'] = new fileField(array(
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
            'm2m' => 'DocumentsCategory',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_categories,
            'add_related' => true,
            'add_related_url' => $this->_home.'?evt['.$this->_controller->getInstanceName().'-manageDoc]&block=category&insert=1',
        ));


        return $structure;
    }

    public function downloadUrl()
    {
        return $this->_controller->getInstanceName().'/download/'.$this->id;
    }
    
    
}

