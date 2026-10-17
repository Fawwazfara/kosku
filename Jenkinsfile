@Library('shared-library@main') _

def deployServer = 'ubuntu@43.156.127.44'
def deployPath   = '/var/www/kosku'

appPipeline(
    type: 'laravel',
    agent: 'docker',
    laravel: [ withNode: true ],
    skipDocker: true
)

node('docker') {
    stage('Deploy: Traditional (Nginx + PHP-FPM)') {
        withCredentials([sshUserPrivateKey(credentialsId: 'tencent-ssh-key', keyFileVariable: 'SSH_KEY')]) {
            sh """
                SSH="ssh -o StrictHostKeyChecking=no -i \$SSH_KEY"
                \$SSH ${deployServer} '
                    cd ${deployPath} &&
                    git pull --ff-only &&
                    composer install --no-dev --optimize-autoloader --no-interaction &&
                    npm ci &&
                    npm run build &&
                    php artisan migrate --force &&
                    php artisan config:cache &&
                    php artisan route:cache &&
                    sudo systemctl reload php8.4-fpm
                '
            """
        }
    }
}
