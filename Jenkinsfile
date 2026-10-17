@Library('shared-library@main') _

def dockerImage  = '1237050038/kosku'
def deployServer = 'ubuntu@43.156.127.44'
def deployPath   = '~/kosku'

appPipeline(
    type: 'laravel',
    agent: 'docker',
    laravel: [ withNode: true ],
    docker: [ imageName: dockerImage, additionalTags: ['latest'] ]
)

node('docker') {
    stage('Deploy: Tencent Cloud') {
        withCredentials([sshUserPrivateKey(credentialsId: 'tencent-ssh-key', keyFileVariable: 'SSH_KEY')]) {
            sh """
                SSH="ssh -o StrictHostKeyChecking=no -i \$SSH_KEY"
                SCP="scp -o StrictHostKeyChecking=no -i \$SSH_KEY"
                \$SSH ${deployServer} "mkdir -p ${deployPath}"
                \$SCP compose.prod.yml ${deployServer}:${deployPath}/compose.prod.yml
                \$SSH ${deployServer} "cd ${deployPath} && docker compose -f compose.prod.yml pull && docker compose -f compose.prod.yml up -d --remove-orphans && docker compose -f compose.prod.yml exec -T app php artisan migrate --force && docker image prune -f"
            """
        }
    }
}
