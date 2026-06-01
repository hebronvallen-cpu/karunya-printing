<?php

namespace App\Models\Concerns;

trait MemakaiAliasKolom
{
    public function getAttribute($key)
    {
        return parent::getAttribute($this->namaKolomAsli($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute($this->namaKolomAsli($key), $value);
    }

    private function namaKolomAsli($key)
    {
        if (is_string($key) && isset($this->aliasKolom[$key])) {
            return $this->aliasKolom[$key];
        }

        return $key;
    }
}
