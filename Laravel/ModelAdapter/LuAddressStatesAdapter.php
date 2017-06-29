<?php

namespace App\ModelAdapters;

use App\LuAddressStates;

class LuAddressStatesAdapter extends LuAddressStates
{
    public static function getOptions(): array
    {
        $self = new static;

        $options = [];

        foreach ($self->all() as $state) {
            $options[] = [
                'key' => $state->id,
                'value' => $state->state
            ];
        }

        return $options;
    }
}
