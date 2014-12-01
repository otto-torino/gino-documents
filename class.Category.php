<?php
/**
 * @file class.Category.php
 * @brief Class Category
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @version 0.1
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 */

namespace Gino\App\Documents;

/**
 * @ingroup gino-documents
 * Classe tipo @ref Model che rappresenta una categoria di documenti.
 *
 * @version 0.1
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Category extends \Gino\Model
{
    public static $table = 'documents_category';

    /**
     * @brief Costruttore
     * @param int $id id della categoria
     * @param documents $instance istanza del controller
     * @return istanza di @ref Category
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'color' => array(_('Colore'), _('Inserire il codice esadecimale, es. ff0000')),
        );

        parent::__construct($id);

        $this->_model_label = _('Categoria');
    }

    /**
     * @brief Casting a stringa
     *
     * @return rappresentazione a stringa dell'oggetto
     */
    function __toString()
    {
        return (string) $this->ml('name');
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

    /**
     * @brief Array associativo per popolare un input select
     * @param documents $controller istanza del controller
     * @return array associativo id=>nome
     */
    public static function getForSelect($controller)
    {
        $objs = self::objects($controller);
        $res = array();
        foreach($objs as $obj) {
            $res[$obj->id] = \Gino\htmlChars($obj->ml('name'));
        }

        return $res;
    }
}

