<section>
    <h1><?= _('Documenti') ?></h1>
    <div class="row">
        <div class="col-md-8">
            <? if(count($documents)): ?>
            <table class="table table-bordered">
                <tr>
                    <th>Categoria</th>
                    <th><a href="<?= $plink->addParams($base_url, 'o=name', true); ?>">Nome</a> <?= $order == 'name' ? '<span class="fa fa-arrow-circle-up"></span>' : '' ?></th>
                    <th><a href="<?= $plink->addParams($base_url, 'o=filesize', true); ?>">Dimensione</a> <?= $order == 'filesize' ? '<span class="fa fa-arrow-circle-up"></span>' : '' ?></th>
                    <th><a href="<?= $plink->addParams($base_url, 'o=insertion_date', true); ?>">Inserimento</a> <?= $order == 'insertion_date' ? '<span class="fa fa-arrow-circle-up"></span>' : '' ?></th>
                    <th>Descrizione</th>
                </tr>
                <? foreach($documents as $doc): ?>
                    <? 
                        $ctgs = array(); 
                        foreach($doc->categories as $ctg_id) {
                            $ctg = new DocumentsCategory($ctg_id, $doc->getController());
                            $ctgs[] = $ctg->color ? "<span style=\"color: #".$ctg->color."\"class=\"fa fa-circle\"></span> ".htmlChars($ctg->name) : htmlChars($ctg->name);
                        }
                    ?>
                    <tr>
                        <td><?= implode(', ', $ctgs) ?></td>
                         <td><span class="tooltipfull" title="<?= _('Filename').'::'.htmlChars($doc->filename) ?>"><a href="<?= $doc->downloadUrl() ?>"><?= htmlChars($doc->name) ?></a></span></td>
                        <td><?= round($doc->filesize / 1024, 2) ?> kb</td>
                        <td><?= dbDatetimeToDate($doc->insertion_date, '/') ?></td>
                        <td><?= htmlChars($doc->description) ?></td>
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
        <div class="col-md-4">
            <?= $form_search ?>
        </div>
    </div>
</section>
