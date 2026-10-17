@Library('shared-library@main') _

def deployServer = 'ubuntu@43.156.127.44'
def dockerImage  = '1237050038/kosku'

properties([
    parameters([
        choice(name: 'DEPLOY_MODE', choices: ['traditional', 'docker'],
               description: 'traditional = Nginx+PHP-FPM native ; docker = image dari Docker Hub')
    ])
])

def mode = (params.DEPLOY_MODE ?: 'traditional').toString()

if (mode == 'docker') {

    appPipeline(
        type: 'laravel',
        agent: 'docker',
        laravel: [ withNode: true ],
        docker: [ imageName: dockerImage, additionalTags: ['latest'] ]
    )

    node('docker') {
        stage('Deploy: Docker (registry)') {
            withCredentials([sshUserPrivateKey(credentialsId: 'tencent-ssh-key', keyFileVariable: 'SSH_KEY')]) {
                sh """
                    SSH="ssh -o StrictHostKeyChecking=no -i \$SSH_KEY"
                    SCP="scp -o StrictHostKeyChecking=no -i \$SSH_KEY"
                    \$SSH ${deployServer} 'mkdir -p ~/kosku'
                    \$SCP compose.prod.yml ${deployServer}:~/kosku/compose.prod.yml
                    \$SSH ${deployServer} 'cd ~/kosku && docker compose -f compose.prod.yml pull && docker compose -f compose.prod.yml up -d --remove-orphans && docker compose -f compose.prod.yml exec -T app php artisan migrate --force'
                """
            }
        }
    }

} else {

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
                    ssh -o StrictHostKeyChecking=no -i \$SSH_KEY ${deployServer} 'bash -s' < deploy/deploy.sh
                """
            }
        }
    }
}
