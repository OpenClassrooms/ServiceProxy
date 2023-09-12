<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Request;

enum Moment: string
{
    case PREFIX = 'pre';

    case SUFFIX = 'post';

    case EXCEPTION = 'exception';
}
