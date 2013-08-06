data_science_toolkit_php_api_client
===================================
A simple PHP API Client for the Data Science Toolkit project.


API Client Usage
----------------

### Getting Started
- include the client class and instantiate it
- set the base url using `set_base_url()`
	- not specifying a url will default to the open api address
	- you must specify your server's base url when running your own DST server

#### Getting Started Example
```php
require_once( PATH_TO_THIS_CLIENT . '/dst_api_client.php' );
$Dst = new Dst_api_client();
$Dst->set_base_url();
try {
	$address = '2820 Clark Ave. Saint Louis, MO 63103';
	$response = json_decode( $Dst->street2coordinates( $address ), TRUE );
	if( is_null( $response ) )
		throw new Exception( 'Data Science Toolkit returned malformed JSON as a response.' );
} catch ( Exception $e ) {
	die( $e->getMessage() );
}
```

### Available Methods
- `street2coordinates( $address )`
- `google_style_geocoder( $address )`
- `coordinates2politics( $lat, $lon )`

### Additional Endpoints
- see developer docs for additional rest endpoints
- http://www.datasciencetoolkit.org/developerdocs


Running Your Own DST Server
---------------------------

### Launch an AWS Machine Image of the DST
- doesn't work from the web interface ( generally won't find the AMI ID )
- prob want to check documentation for the most recent AMI ID
- from the CLI: `ec2-run-instances ami-7b9df412 -t m1.small -z us-east-1b --aws-access-key <ACCESS-KEY> -W <SECRET-KEY> -k <KEY-FILE> -v`