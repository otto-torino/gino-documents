<?php
/**
 * @file class.Category.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Documents.Category
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 */

namespace Gino\App\Documents;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una categoria di documenti
 *
 * @version 1.0.0
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
     * @param \Gino\App\Documents\documents $instance istanza di Gino.App.Documents.documents
     * @return istanza di Gino.App.Documents.Category
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
     * @brief Rappresentazione a stringa dell'oggetto
     *
     * @return nome categoria
     */
    function __toString()
    {
        return (string) $this->ml('name');
    }

    /**
     * @brief Definizione della struttura del modello
     *
     * @see Gino.Model::structure()
     * @param $id id dell'istanza
     *
     * @return array, struttura del modello
     */
    public function structure($id)
    {
        $structure = parent::structure($id);

        return $structure;
    }

    /**
     * @brief Array associativo per popolare un input select
     * @param \Gino\App\Documents\documents $controller istanza di Gino.App.Documents.documents
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
