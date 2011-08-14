# Hybrid 
A set of class that extends the functionality of FuelPHP without affecting the standard workflow when the application doesn't actually utilize Hybrid feature.

## Installation

Hybrid can be installed using FuelPHP Oil utility, but before we can run the commandline utility *hybrid* and *orm* package need to be included in the autoload packages under ` APPPATH/config/config.php `.

    'always_load'   => array(

        /**
         * These packages are loaded on Fuel's startup.  You can specify them in
         * the following manner:
         *
         * array('auth'); // This will assume the packages are in PKGPATH
         *
         * // Use this format to specify the path to the package explicitly
         * array(
         *     array('auth' => PKGPATH.'auth/')
         * );
         */
        'packages'  => array(
            'orm',
            'hybrid',
        ),

Now we can start the installation by running ` php oil refine hybrid:install ` or browse the help from ` php oil refine hybrid:help ` , you will be ask a few configuration how you would like to configure your users database including Facebook Connect and Twitter OAuth option.

Once model and migrations script generated, please update ` APPPATH/config/app.php ` based on your installation preference.