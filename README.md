# Requirements: 
- PHP server/parser (wampp/xampp/apache)

# Step by step guide
- Edit files in `target` folder only! 
- run `localhost/SSOpath/generate.php?lang=en`
- `lang_DATA.data` will be generated (may require multiply F5 to full generate because of memory issues depending on machine)
- move `lang_DATA.data` do `/data` folder in Saint Seiya Reborn client
- run game via `bin/seiya.exe` (DON't run game via `lancher.exe` or lang file will be deleted!)
- test your changes in game

# Important informations
- Line structure has to be preserved: `id\ttext...` where `\t` is tabulator
- Do not modify `/ids` and `/source` folders. They may be used only to fetch original Chinese translations.

# To create new language package
- clone english (or any other lang you like) folder `en` and name it as target language shortcut (`de`,`it` etc)
- generate file via `generate.php?lang=TARGET_LANG` where `TARGET_LANG` is language shortcut (`de`,`it` etc)

# Google translate
- Be careful with google translate. It breaks file structure (removes tabulations) and often removes one of special chars like `^%&$`. 
- color codes has to be fixed in most cases `^ FF0000` -> `^FF0000`
- `data_quest.txt` links requires heavy fixing as spaces between `&&` are not supported. eg `&pos:(-300.93,350,287.2)Underworld Spy&` is not correct! Either make it `Underworld &pos:(-300.93,350,287.2)Spy&` or `&pos:(-300.93,350,287.2)Underworld_Spy&`

# Generator debug info
- generate.php contains basic debugger that will point broken file structure or mismatches regarding special characters (`$%&{}`) 

# List of things that does not work with language file (so far)
- monster dialog messages during fights 
- system event announcements
- map names above minimap
- some of skill description hidden bonuses

Good luck for all translators!
