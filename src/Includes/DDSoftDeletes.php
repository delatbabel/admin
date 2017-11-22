<?php

namespace Delatbabel\Admin\Includes;

use Illuminate\Database\Eloquent\SoftDeletes;

trait DDSoftDeletes
{
    use SoftDeletes;

    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }
}
