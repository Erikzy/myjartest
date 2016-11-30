# myjartest
myjartest

1) Required:


   a) Minimum of PHP 5.6
   
   b) Apache Server with mod_rewrite enabled

2) Installation :


    a) Pull the repository
    
    b) Set up Apache to serve the directory you pulled the repository into:
   
   ------EXAMPLE APACHE CONF----
    <VirtualHost *:80>
    DocumentRoot "/var/www/myjar2/"
    DirectoryIndex index.php
    ServerName myjar.blackbutterfly.ee

    <Directory "/var/www/myjar2/">
      AllowOverride All
      Allow from All
    </Directory>
   </VirtualHost>
  --------------------------------
  
   c) edit StaticConf.class.php for environment-specific values such as DB-configuration and site URL.
   
   d) run composer to verify external dependencies
   
   e) import the example database dump "myjar_2016-11-30.sql"
   
3) Usage:


   a) GET {url}/customer/ - returns list of customers
   
   b) GET {url}/customer?key1=val1&key2=val2 - returns list of customers filtered based on given parameters
   
   c) GET {url}/customer/{id} - returns customer with id specified
   
   d) POST {url}/customer/ - creates a customer based on the post json
   
4) JSON example:
  
  {
    "phone":"+447700900713",
    "email":"erqwhhh3d@gmail.com",
    "firstname":"eissk",
    "lastname":"kesssa",
    "product1":"prossduct",
    "interest":"20",
    "goal":"goal",
    "favoriteGame":"witcaaher",
    "favoritesong":"Lok'tar Ogar",
    "programminglang":"php"
  }
  
 5) available filters:
 
       a) sort      - field on which to sort (requires valid field name in the parent entity)
       
       b) direction - direction on which to sort. default is ASC accepted (ASC and DESC)
       
       c) limit     - limit the size of the result, requires positive integer
       
       d) offset    - offset of the result (requires a valid limit and a positive integer)
       
       e) {fieldname} - filters based on a specific field value, use *val* to get partial values

 6) Database structure:
 
      customer:
         -id [PK][AI]
         -email [UNIQUE] VARCHAR
      
      customerdetail:
         -id [PK][AI]
         -customer_id [FK][references: customer.id]
         -identifiername VARCHAR
         -identifiervalue VARCHAR
       
       phonenunber:
         -customer_id[PK][references: customer.id]
         -phonenumber TEXT
         
   
