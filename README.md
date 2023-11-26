# Pottery Forming Technology Database #
Contributors: Caroline Jeffra

Creates database and functionality for describing and managing data relating to pottery forming technology.

# Description #
Creates database and functionality for describing and managing data relating to pottery forming technology. Functionality within wp-admin is added to import and export CSVs, and custom API endpoints are registered for CRUD actions on the data.

# Features #
* CSV import (see template below)
* CSV export
* Custom API endpoints (see documentation below)
* Custom access to viewing records for different users [planned]
* Custom views for presenting database content [planned]
* Flexibility in including media types [planned]

# CSV Import #
Data in CSVs must be structured properly for a successful import and for new data to integrate well with existing data. Please adhere to the guidelines below for best results.

## Separators and Enclosures
Values must be separated by a comma { , } and text fields containing punctuation must be enclosed in double quotes { "string, containing punctuation" }.

## Headers
CSV files for import are expected to have the following headers:
- pot_type
- forming_method
- shape
- catalog_number
- traces_observed

These headers must be specified within your CSV file, e.g.:

```
import.csv

"pot_type","forming_method","shape","catalog_number","traces_observed"
"experiment",1,"straight sided cup","E1","fine to med rilling,dented effect from thickness discontinuities,thickness discontinuities,torsional rippling"
"experiment",1,"straight sided cup","E2","fine to med rilling,dented effect from thickness discontinuities,thickness discontinuities,torsional rippling"

...
```

It is entirely possible that these columns are not expansive enough for all who might use this plugin. In that case, please [raise an issue](https://github.com/caroline-jeffra/pottery-forming-tech-db-plugin/issues).

# Custom API endpoints
Five custom endpoints are available upon activation of this plugin:
1. GET all records
2. GET one record
3. POST a new record
4. PATCH an existing record
5. DELETE a record

## GET
The GET endpoints are currently accessible to all users, with plans to allow for further control of this. This means that if you enter sensitive research data, the API will automatically allow non-credentialled site visitors to view it: please proceed with caution.

### GET all
Returns all records to any user, available at:

`{your wordpress base url}/wp-json/pottery-forming-tech-api/v1/pots`

### GET one
Returns one records to any user based on the id number provided, available at:

`{your wordpress base url}/wp-json/pottery-forming-tech-api/v1/pot/{pot id number}`

## POST
Creates a new record based on form data included in the POST request. This can only be completed by logged in users who have permissions to create posts, available at:

`{your wordpress base url}/wp-json/pottery-forming-tech-api/v1/pot`

## PATCH
Updates an existing record based on form data included as a body in the PATCH request and the pot id number included in the endpoint address. This can only be completed by logged in users who have permissions to edit private posts, available at:

`{your wordpress base url}/wp-json/pottery-forming-tech-api/v1/pot/{pot id number}`

## DELETE
Deletes an existing record based on the pot id number included in the endpoint address. This can only be completed by logged in users who have permissions to delete private posts, and is available at:

`{your wordpress base url}/wp-json/pottery-forming-tech-api/v1/pot/{pot id number}`

# Bug Reporting and Feature Requests
The process for reporting bugs or requesting features is to [raise an issue](https://github.com/caroline-jeffra/pottery-forming-tech-db-plugin/issues) within the github repository. During development, priority will be given to completing the core functionality of the plugin (as specified in the Features section above) and squashing breaking bugs. After initial development, priority will be given to feature building to make the plugin more widely applicable to managing data on pottery forming technology.
