# Lychee-FlashAir

Lychee-FlashAir is a plugin for [Lychee](https://github.com/electerious/Lychee),
to import photos from a Toshiba FlashAir wifi sd card.

## Installation

Change to the plugin directory (`plugins/`) of your Lychee installation and run the following commands:

```
git clone https://github.com/mhp/Lychee-FlashAir.git
./Lychee-FlashAir/install.sh
```

## Configuration

The DNS name for the wifi SD card is hardcoded in `index.php` along with the directory to sync from.

## Use

Provided your version of Lychee handles `Server::import` events, then when you perform an
`Import from server`, this plugin will download the images from the SD card first.  Images
are deleted from the SD card once they have been downloaded (but before they have been imported).
