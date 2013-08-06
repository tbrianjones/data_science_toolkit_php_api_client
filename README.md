data_science_toolkit_php_api_client
===================================
A simple PHP API Client for the Data Science Toolkit project.


API Client Usage
----------------

### getting started
- include the client class and instantiate it
- set the base url using `set_base_url()`
	- not specifying a url will default to the open api address

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
- prob want to check documentation for the most recent api id
- from the CLI: `ec2-run-instances ami-7b9df412 -t m1.small -z us-east-1b --aws-access-key <ACCESS-KEY> -W <SECRET-KEY> -k <KEY-FILE> -v`