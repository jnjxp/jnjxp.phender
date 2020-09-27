<?php

return [
    'url' => function (string $slug) : string {
        return "/articles/$slug";
    }
];
