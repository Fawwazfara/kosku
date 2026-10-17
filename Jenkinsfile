@Library('shared-library@main') _

appPipeline(
    type: 'laravel',
    agent: 'docker',
    laravel: [ withNode: true ],
    docker: [ imageName: '1237050038/kosku' ],
    deployServer: 'ubuntu@43.156.127.44',
    credentialsId: 'tencent-cloud-ssh'
)
