<?php

namespace FFDB\Instance\Filter;

class Stats
{
    public $rules_started = 0;
    public $rules_skipped = 0;
    public $rules_used = 0;
    public $rules_success = 0;
    public $rules_missed = 0;

    public $document_checked = 0;
    public $document_skipped = 0;
    public $document_found = 0;
    public $document_missed = 0;

    public $runtime = 0;
}