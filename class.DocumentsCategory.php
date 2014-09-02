<?php
/**
 * @file class.DocumentsCategory.php
 * @brief Class DocumentsCategory
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @version 0.1
 * @date 2014-03-06
 */
class DocumentsCategory extends Model
{
    private $_controller;
    public static $table = 'documents_category';

    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'color' => array(_('Colore'), _('Inserire il codice esadecimale, es. ff0000')),
        );

        parent::__construct($id);

        $this->_model_label = _('Categorie');
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

        return $structure;
    }

    public static function getForSelect($controller)
    {
        $objs = self::objects($controller);
        $res = array();
        foreach($objs as $obj) {
            $res[$obj->id] = htmlChars($obj->name);
        }

        return $res;
    }
}

