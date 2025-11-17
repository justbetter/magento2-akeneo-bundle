# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient
quality to benefit the project. Many developers have different skillsets, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open
source projects are used by many developers, who may have entirely different needs to your own. Think about
whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **[Magento Coding Standard](https://github.com/magento/magento-coding-standard)** - Follow Magento 2 coding standards (based on PSR-12 with Magento-specific rules)
    
- **Code Quality Tools** - Run these commands before committing:
    ```bash
    composer install
    
    # Run all checks with GrumPHP (code style + static analysis)
    composer grumphp
    
    # Or run checks individually:
    composer codestyle       # Check code style
    composer codestyle:fix   # Auto-fix code style issues
    composer analyse         # Run PHPStan static analysis
    ```
    
    **Note:** GrumPHP runs automatically on `git commit` and will block commits with code quality issues (both code style and static analysis).

- **Document any change in behaviour** - Make sure the `README.md`, `FEATURES.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](https://semver.org/).

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Test in a Magento environment** - Ensure your changes work correctly with:
  - Magento 2.4.4+
  - PHP 8.2, 8.3, 8.4
  - Akeneo Connector Community Edition

**Happy coding**!