# Roconut

A [Symfony](https://symfony.com/) based pastebin website for BZFlag `/savemsgs` log files. By default, the exported message logs contain ANSI escape codes, which aren't straightforward to remove and if they are removed, then you're left with a colorless message log. Then you are left to upload the message log to another pastebin website. This project aims to fix all of those issues:

- Upload all your message logs
- Apply filters to remove unwanted console messages (e.g. flag grabs, team chat, server messages)
- Display only certain private messages conversations
- Have a shareable link for your message to give out to others
- Save all your message logs in one location
- Your existing BZFlag account is all that's needed; no need to remember a new password
- No need for users to download, install, or compile any software; it's all handled on the website

## Core Components

The Roconut project builds on top of two core components, which is what is used for handling and colorizing message logs. These components may be able to exist as standalone forks/projects in the future.

### ANSI HTML Transformer

The [AnsiHtmlTransformer](src/AppBundle/Service/AnsiHtmlTransformer.php) component is a modified version of [SensioLabs's project](https://github.com/sensiolabs/ansi-to-html) and all modifications are available under the MIT license. This component is tasked with parsing ANSI escape codes and converting them to HTML.

The transformer supports both escape codes for colors (30-38) and ANSI RGB values.

- Color values parsed from escape codes are handled with a CSS class in the span elements: `ansi_color_fg_<color name>`
- RGB values are converted into HEX values and displayed under the `style` attribute of span elements.

### Message Log Transformer

The [MessageLogTransformer](src/AppBundle/Service/MessageLogTransformer.php) component is a class which parses and manipulates the HTML received from AnsiHtmlTransformer. At its core, it is a very delicate class with a list of complex regular expressions used to compare messages and filter them out as requested. This class will likely not work by itself and depends heavily on the format that AnsiHtmlTransformer returns.

## License

[MIT](LICENSE.md)
