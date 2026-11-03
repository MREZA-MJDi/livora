<?php

return [
    /*
     * Province/city names are sourced from the MIT-licensed dataset documented
     * in resources/data/README.md. Keep the data local: checkout must not depend
     * on a third-party geocoding API.
     */
    'provinces' => json_decode(
        file_get_contents(resource_path('data/iran-provinces-cities.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    ),
];
