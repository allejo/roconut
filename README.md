# Roconut

[![Build Status](https://travis-ci.org/allejo/roconut.svg?branch=master)](https://travis-ci.org/allejo/roconut)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/allejo/roconut/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/allejo/roconut/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/allejo/roconut/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/allejo/roconut/?branch=master)

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

The [MessageLogTransformer](src/AppBundle/Service/MessageLogTransformer.php) component is a class which parses and manipulates the HTML received from AnsiHtmlTransformer. At its core, it is a very delicate class that attempts to standardize message log to a format that separate message filters can use to filter out respective messages. This class will likely not work by itself and depends heavily on the format that AnsiHtmlTransformer returns.

Message filters are separate based on their purpose and are located in the `AppBundle\MessageLogFilter` namespace and must extend the `MessageLogFilterInterface`.

- The `shouldRun()` method will be given the AND'd filter flags and should check if the respective filter has been requested; e.g. `$flags & MessageLogTransformer::HIDE_FLAG_ACTION`
- The `filterLine()` method is run whenever the filter is used. If this method returns `true`, then propagation to the rest of the filters will be stopped and `$rawLine` should set to an empty string (something you need to manually do). Returning `false` will allow you to manipulate `$rawLine` and allow other filters to process this line also.

Message filters are run in **no** particular order.

## License

[MIT](LICENSE.md)
