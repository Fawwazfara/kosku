@Library('shared-library@main') _

def deployServer = 'ubuntu@43.156.127.44'

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
