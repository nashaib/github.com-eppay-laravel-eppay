# Publishing to Packagist

This guide explains how to publish the EpPay Laravel package to Packagist so developers can install it via Composer.

## Prerequisites

1. A GitHub account
2. A Packagist account (sign up at https://packagist.org/)
3. Git installed on your machine
4. The package is ready and tested

## Step 1: Create GitHub Repository

1. Go to GitHub and create a new repository:
   - Repository name: `laravel-eppay`
   - Description: "Laravel package for easy EpPay cryptocurrency payment integration"
   - Visibility: Public
   - Don't initialize with README (we have our own)

2. Copy the repository URL (e.g., `git@github.com:eppay/laravel-eppay.git`)

## Step 2: Initialize Git Repository

From the package directory:

```bash
cd packages/eppay/laravel-eppay

# Initialize git repository
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit - EpPay Laravel Package v1.0.0"

# Add remote repository
git remote add origin git@github.com:eppay/laravel-eppay.git

# Push to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Create a Release Tag

Tags are important for versioning:

```bash
# Create and push version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

## Step 4: Submit to Packagist

1. Log in to [Packagist.org](https://packagist.org/)

2. Click "Submit" in the top menu

3. Enter your GitHub repository URL:
   ```
   https://github.com/eppay/laravel-eppay
   ```

4. Click "Check" to validate the repository

5. If validation passes, click "Submit"

## Step 5: Set Up Auto-Updates

To automatically update Packagist when you push to GitHub:

### Option A: GitHub Service Hook (Recommended)

1. Go to your GitHub repository settings
2. Click "Webhooks" → "Add webhook"
3. Set the payload URL to: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
4. Set content type to `application/json`
5. Set secret to your Packagist API token (found in your Packagist profile)
6. Select "Just the push event"
7. Click "Add webhook"

### Option B: Packagist API Token

1. Go to your Packagist profile
2. Click "Show API Token"
3. Copy the token
4. Go to your GitHub repository settings
5. Go to "Secrets and variables" → "Actions"
6. Add new secret: `PACKAGIST_TOKEN` with your token as value

## Step 6: Verify Installation

Test that your package can be installed:

```bash
# In a fresh Laravel project
composer require eppay/laravel-eppay
```

## Releasing New Versions

When you want to release a new version:

1. **Update the CHANGELOG.md** with new changes

2. **Commit changes:**
```bash
git add .
git commit -m "Version 1.1.0 - Add new features"
git push
```

3. **Create new tag:**
```bash
git tag -a v1.1.0 -m "Release version 1.1.0"
git push origin v1.1.0
```

4. Packagist will automatically detect the new version (if webhook is set up)

## Semantic Versioning

Follow [Semantic Versioning](https://semver.org/) (MAJOR.MINOR.PATCH):

- **MAJOR**: Breaking changes (v1.0.0 → v2.0.0)
- **MINOR**: New features, backward compatible (v1.0.0 → v1.1.0)
- **PATCH**: Bug fixes, backward compatible (v1.0.0 → v1.0.1)

Examples:
- `v1.0.0` - Initial release
- `v1.0.1` - Bug fix release
- `v1.1.0` - New feature release (backward compatible)
- `v2.0.0` - Major update with breaking changes

## Package Statistics

After publishing, you can track:

- Download counts on Packagist
- GitHub stars and forks
- Issues and pull requests

Monitor your package at: `https://packagist.org/packages/eppay/laravel-eppay`

## Best Practices

1. **Always test before releasing** - Run tests, check examples
2. **Update documentation** - Keep README and CHANGELOG current
3. **Use semantic versioning** - Helps users understand changes
4. **Write good commit messages** - Clear and descriptive
5. **Respond to issues** - Help users with problems
6. **Accept pull requests** - Community contributions are valuable

## Promotion

After publishing:

1. Announce on social media (Twitter, LinkedIn)
2. Post on Laravel News
3. Share in Laravel communities (Reddit, Discord, Slack)
4. Write a blog post about the package
5. Create a video tutorial

## Troubleshooting

### "Could not find package"

- Ensure the package name in composer.json matches Packagist
- Wait a few minutes after submission
- Check that the repository is public

### "The package is not auto-updated"

- Verify webhook is configured correctly
- Check webhook deliveries in GitHub settings
- Manually trigger update on Packagist if needed

### "Version constraint is not satisfied"

- Check PHP version requirements
- Check Laravel version requirements
- Ensure all dependencies are available

## Support

If you encounter issues publishing:

- Packagist Help: https://packagist.org/about
- GitHub Help: https://docs.github.com/
- Email: support@eppay.io

---

Once published, developers can install your package with:

```bash
composer require eppay/laravel-eppay
```

Congratulations on publishing your Laravel package! 🎉
