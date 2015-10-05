Schema Warden is composed of three parts:

  * **SchemaQuery**
    * query nested-arrays for data, by reference
  * **SchemaMapper**
    * reduces an array, keying it by dot-notation
  * **SchemaWarden**
    * require existence, validate, or transform data

At Prescreen, we use the Schema Warden library for a number of tasks:

  * parse inputs to our API
  * validate and transform data going into the database (in our case, Mongo)
  * query responses from Facebook's Graph API