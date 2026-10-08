<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JournalModel;

/**
 * Consultation du journal des actions d'administration, filtrable et paginée.
 */
class Journal extends BaseController
{
    private const PAR_PAGE = 25;

    public function index(): string
    {
        $journal = model(JournalModel::class);
        $types   = $journal->valeurs('typeAction');
        $tables  = $journal->valeurs('tableCible');

        // Seules les valeurs présentes dans le journal sont acceptées comme filtres
        $type  = in_array($this->request->getGet('type'), $types, true) ? $this->request->getGet('type') : null;
        $table = in_array($this->request->getGet('table'), $tables, true) ? $this->request->getGet('table') : null;

        return view('admin/journal', [
            'titre'   => 'Journal des actions',
            'lignes'  => $journal->filtre($type, $table)->paginate(self::PAR_PAGE),
            'pager'   => $journal->pager,
            'types'   => $types,
            'tables'  => $tables,
            'type'    => $type,
            'table'   => $table,
        ]);
    }
}
