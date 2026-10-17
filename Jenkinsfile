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
        sshagent(credentials: ['tencent-ssh-key']) {
            sh """
                ssh -o StrictHostKeyChecking=no ${deployServer} 'mkdir -p ${deployPath}'
                scp -o StrictHostKeyChecking=no compose.prod.yml ${deployServer}:${deployPath}/compose.prod.yml
                ssh -o StrictHostKeyChecking=no ${deployServer} '
                    cd ${deployPath} &&
                    docker compose -f compose.prod.yml pull &&
                    docker compose -f compose.prod.yml up -d --remove-orphans &&
                    docker compose -f compose.prod.yml exec -T app php artisan migrate --force &&
                    docker image prune -f
                '
            """
        }
    }
}
