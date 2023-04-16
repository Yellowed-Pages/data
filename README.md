# Data | Yellowed Pages
Command-line utilities to manage searchable entities in Yellowed Pages.

## Installation
Clone this repository or download the code manually:
```bash
git clone https://github.com/Yellowed-Pages/data.git
```
Install dependencies through [Composer](https://getcomposer.org/):
```bash
cd data
composer update
```

## Configuration
Copy the configuration template file `config-dummy.ini` into a file of your choice, such as `config.ini` and edit as needed:
* **FileSystem/entities** path to folder of entities
* **ElasticSearch/host, port, username, password, index** connection parameters to ElasticSearch server


## Usage
To manage the ElasticSearch index, refer to `bin/index-manager.php`:
```bash
# Creates all the necessary infrastructure on ElasticSearch
bin/index-manager.php config.ini create

# Removes infrastructure from ElasticSearch
# WARNING: All data will be lost!
bin/index-manager.php config.ini delete

# Recreates necessary infrastructure on ElasticSearch
# WARNING: All data will be lost!
bin/index-manager.php config.ini reset
```

To manage data, refer to `bin/data-manager.php`:
```bash
# Shows content of local entity "spell:fireball"
bin/data-manager.php config.ini spell:fireball

# List every local entity
bin/data-manager.php config.ini list

# List every local entity of type "spell"
bin/data-manager.php config.ini list spell

# Runs an integrity check over local entities
bin/data-manager.php config.ini check

# Sends every local entity to remote ElasticSearch
bin/data-manager.php config.ini index

# Sends local entity "spell:fireball" to remote ElasticSearch
bin/data-manager.php config.ini index spell:fireball
```