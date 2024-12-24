<?php

namespace App\Enum;

enum AiModel: string
{
    case OpenHermesMistral7B = 'OpenHermes-2.5-Mistral-7B';
    case Fimbulvetr11Bv2 = 'Fimbulvetr-11B-v2';
    case LLaMA213BEstopia = 'LLaMA2-13B-Estopia';
    case Llama318BInstruct = 'Meta-Llama-3.1-8B-Instruct';
}
