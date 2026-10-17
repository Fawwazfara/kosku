@Library('shared-library@main') _

appPipeline(
    type: 'laravel',
    agent: 'docker',
    laravel: [ withNode: true ],           // <- tambah ini
    deployServer: 'ubuntu@43.156.127.44',
    credentialsId: 'tencent-cloud-ssh'
)
