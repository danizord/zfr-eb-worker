<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrEbWorkerTest\Container;

use Aws\Sdk as AwsSdk;
use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface;
use ZfrEbWorker\Container\QueuePublisherFactory;
use ZfrEbWorker\Exception\RuntimeException;
use ZfrEbWorker\Publisher\QueuePublisherInterface;

class QueuePublisherFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionIfNoConfig()
    {
        $this->setExpectedException(RuntimeException::class);

        $container = $this->getMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with('config')->willReturn([]);

        $factory = new QueuePublisherFactory();

        $factory->__invoke($container);
    }

    public function testFactory()
    {
        $container = $this->getMock(ContainerInterface::class);
        $container->expects($this->at(0))->method('get')->with('config')->willReturn([
            'zfr_eb_worker' => [
                'queues' => []
            ]
        ]);

        $sqsClient = $this->getMock(SqsClient::class, [], [], '', false);

        $awsSdk = $this->getMock(AwsSdk::class, ['createSqs']);
        $awsSdk->expects($this->once())->method('createSqs')->willReturn($sqsClient);

        $container->expects($this->at(1))->method('get')->with(AwsSdk::class)->willReturn($awsSdk);

        $factory        = new QueuePublisherFactory();
        $queuePublisher = $factory->__invoke($container);

        $this->assertInstanceOf(QueuePublisherInterface::class, $queuePublisher);
    }
}