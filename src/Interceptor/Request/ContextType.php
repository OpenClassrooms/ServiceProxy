<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Request;

enum ContextType: string
{
    case PREFIX = 'pre';

    case SUFFIX = 'post';

    case EXCEPTION = 'exception';
}
