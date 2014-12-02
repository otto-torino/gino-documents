<?php
/**
* @file documents_archive.php
* @brief Template per la vista archivio documenti
*
* Variabili disponibili:
* - **documents**: array, oggetti Gino.App.Documents.Document
* - **pagination**: pagine e riassunto elementi paginazione (i.e. 1-10 di 100)
* - **search_params**: array, parametri di ricerca (chiave => valore)
* - **router**: \Gino\Router, istanza della classe @ref Gino.Router
* - **order**: string, campo di ordinamento
* - **dir**: string, direzione di ordinamento
* - **form_search**: html, form di ricerca
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Documents; ?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= _('Documenti') ?></h1>
    <?= $form_search ?>
    <? if(count($documents)): ?>
        <table class="table table-bordered">
            <tr>
                <th><?= _('Categoria') ?></th>
                <th style="white-space: nowrap"><a href="<?= $router->transformPathQueryString($search_params + array('o'=>'name', 'd'=>($dir == 'desc' ? 'asc' : 'desc'))); ?>">Nome</a> <?= $order == 'name' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
                <th style="white-space: nowrap"><a href="<?= $router->transformPathQueryString($search_params + array('o'=>'filesize', 'd'=>($dir == 'desc' ? 'asc' : 'desc'))); ?>">Dimensione</a> <?= $order == 'filesize' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
                <th style="white-space: nowrap"><a href="<?= $router->transformPathQueryString($search_params + array('o'=>'insertion_date', 'd'=>($dir == 'desc' ? 'asc' : 'desc'))); ?>">Inserimento</a> <?= $order == 'insertion_date' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
                <th><?= _('Descrizione') ?></th>
            </tr>
            <? foreach($documents as $doc): ?>
                <?
                    $ctgs = array();
                    foreach($doc->categories as $ctg_id) {
                        $ctg = new Category($ctg_id, $doc->getController());
                        $ctgs[] = $ctg->color ? "<span style=\"color: #".$ctg->color."\"class=\"fa fa-circle\"></span> ".\Gino\htmlChars($ctg->ml('name')) : \Gino\htmlChars($ctg->ml('name'));
                    }
                ?>
                <tr>
                    <td><?= implode(', ', $ctgs) ?></td>
                    <td><span class="tooltipfull" title="<?= _('Filename').'::'.\Gino\htmlChars($doc->filename) ?>"><a href="<?= $doc->downloadUrl() ?>"><?= \Gino\htmlChars($doc->ml('name')) ?></a></span></td>
                    <td><?= round($doc->filesize / 1024, 2) ?> kb</td>
                    <td><?= \Gino\dbDatetimeToDate($doc->insertion_date, '/') ?></td>
                    <td><?= \Gino\htmlChars($doc->ml('description')) ?></td>
                </tr>
            <? endforeach ?>
        </table>
        <?= $pagination ?>
    <? else: ?>
        <p><?= _('Non risultano documenti registrati') ?></p>
    <? endif ?>
</section>
<? // @endcond ?>
