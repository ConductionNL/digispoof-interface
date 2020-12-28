Digispoof Interface
--------

Digispoof is an application that mocks the DigiD login.

Digispoof provides a list of citizens that is provided by the BRPService.
When selecting one of the citizens from the dropdown menu digispoof will then do an POST request to the provided responseUrl.
In the post will be the bsn of the chosen citizen.

The bsn can used to get a set of information about that citizen from the BRPService.

All the information received is fictive but does represent what you would receive from DigiD.

Digispoof supports the following query parameters:

**responseUrl:**
> This parameter is required.
>
> This url will be used as the POST action in the html form.
> The application will then have to process the bsn provided by digispoof in this url.

**backUrl:**
> This parameter is optional.
>
> when provided it will also send back_url back to the provided responceUrl.
> Which can then be used to redirect your user to the provided backUrl in your application.

An example of what the url might be like:

`https://zuid-drecht.nl/digispoof?responseUrl=https://zuid-drecht.nl/auth/digispoof&backUrl=https://zuid-drecht.nl/`


More information about the BRP mock can be found [here](https://https://github.com/ConductionNL/brpservice).



Credits
-------

Created by [Ruben van der Linde](https://www.conduction.nl/team) for conduction. But based on [api platform](https://api-platform.com) by [Kévin Dunglas](https://dunglas.fr). Commercial support for common ground components available from [Conduction](https://www.conduction.nl).
