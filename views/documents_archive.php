<?php
/**
* @file view/documents_archive.php
* @ingroup gino-documents
* @brief Template per la vista archivio documenti
*
* Variabili disponibili:
* - **documents**: array di oggetti @ref Document
* - **pagination_summary**: riassunto elementi paginazione (i.e. 1-10 di 100)
* - **pagination_navigation**: pagine e navigazione
* - **search_params**: parametri di ricerca
* - **plink**: istanza della classe @ref \Gino\Link
* - **base_url**: url pagina
* - **order**: campo di ordinamento
* - **dir**: direzione di ordinamento
* - **form_search**: form di ricerca
*
* @version 0.1
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
            <th style="white-space: nowrap"><a href="<?= $plink->addParams($base_url, 'o=name&d='.($dir == 'desc' ? 'asc' : 'desc'), true); ?>">Nome</a> <?= $order == 'name' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
            <th style="white-space: nowrap"><a href="<?= $plink->addParams($base_url, 'o=filesize&d='.($dir == 'desc' ? 'asc' : 'desc'), true); ?>">Dimensione</a> <?= $order == 'filesize' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
            <th style="white-space: nowrap"><a href="<?= $plink->addParams($base_url, 'o=insertion_date&d='.($dir == 'desc' ? 'asc' : 'desc'), true); ?>">Inserimento</a> <?= $order == 'insertion_date' ? '<span class="fa fa-arrow-circle-'.($dir == 'desc' ? 'down' : 'up').'"></span>' : '' ?></th>
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

    <div class="">
        <div class="left">
            <?= $pagination_navigation ?>
        </div>
        <div class="right">
            <?= $pagination_summary ?>
        </div>
        <div class="null"></div>
        </div>

        <? else: ?>
        <p><?= _('Non risultano documenti registrati') ?></p>
        <? endif ?>
    </div>
</section>
<? // @endcond ?>
