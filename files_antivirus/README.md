#Owncloud Antivirus App   

files_antivirus is an antivirus app for [Owncloud](https://github.com/owncloud) based on [ClamAV](http://www.clamav.net).

v0.3

##Details

The idea is to check for virus at upload-time, notifying the user (on screen and/or email) and
remove the file if it's infected.

##Status

The App is not complete yet, the following works/is done:
* It can be configured to work with the executable or the daemon mode of ClamAV
* In daemon mode, it sends files to a remote/local server using INSTREAM command
* When the user uploads a file, it's checked
* If an uploaded file is infected, it's deleted and a notification is shown to the user on screen and an email is sent with details.
* Tested in Linux only
* Background Job to scan all files

##In progress

* Test uploading from clients

##ToDo

* File size limit
* Configurations Tuneups
* Other OS Testing
* Look for ideas :P

## Requirements

* Owncloud 4
* ClamAV (Binaries or a server running ClamAV in daemon mode)

## Install

* Install and enable the App
* Go to Admin Panel and configure the App


Authors:
[Manuel Delgado López](https://github.com/valarauco/) :: manuel.delgado at ucr.ac.cr
[Bart Visscher](https://github.com/bartv2/)
