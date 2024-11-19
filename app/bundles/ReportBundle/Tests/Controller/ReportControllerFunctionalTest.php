<?php

namespace Mautic\ReportBundle\Tests\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\ReportBundle\Entity\Report;

class ReportControllerFunctionalTest extends MauticMysqlTestCase
{
    public function testContactReportSqlInjectionDontWork(): void
    {
        $report = new Report();
        $report->setName('Contact report');
        $report->setDescription('<b>This is allowed HTML</b>');
        $report->setSource('leads');
        $coulmns = [
            'l.firstname',
            'l.lastname',
            'l.email',
            'l.date_added',
        ];
        $report->setColumns($coulmns);

        $this->getContainer()->get('mautic.report.model.report')->saveEntity($report);

        // Check sql injection in parameter orderby
        $this->client->request('GET', '/s/reports/view/'.$report->getId().'?tmpl=list&name=report.'.$report->getId().'&orderby=a_id\'');
        $this->assertStringNotContainsString(
            'You have an error in your SQL syntax',
            $this->client->getResponse()->getContent()
        );

        // Check sql injection in parameter name
        $this->client->request('GET', '/s/reports/view/'.$report->getId().'?tmpl=list&name=report.'.$report->getId().'\'&orderby=a_id');
        $this->assertStringNotContainsString(
            'You have an error in your SQL syntax',
            $this->client->getResponse()->getContent()
        );

        // Check sql injection in parameter tmpl
        $this->client->request('GET', '/s/reports/view/'.$report->getId().'?tmpl=list\'&name=report.'.$report->getId().'&orderby=a_id');
        $this->assertStringNotContainsString(
            'You have an error in your SQL syntax',
            $this->client->getResponse()->getContent()
        );

        // Check sql injection in parameter id
        $this->client->request('GET', '/s/reports/view/'.$report->getId().'\'?tmpl=list&name=report.'.$report->getId().'&orderby=a_id');
        $this->assertStringNotContainsString(
            'You have an error in your SQL syntax',
            $this->client->getResponse()->getContent()
        );

        $this->client->request('GET', '/s/reports/view/'.$report->getId().'?tmpl=list&name=report.'.$report->getId().'&orderby=a_id');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testXssUrlFromQuery(): void
    {
        $report = new Report();
        $report->setName('Hits report');
        $report->setDescription('<b>Text Xss Hits</b>');
        $report->setSource('page.hits');
        $coulmns = [
            'ph.isp',
            'ph.url',
            'ph.browser_languages',
            'ph.referer',
            'ph.remote_host',
            'ph.user_agent',
        ];
        $report->setColumns($coulmns);
        $this->getContainer()->get('mautic.report.model.report')->saveEntity($report);
        $xssHeader     = '<script>alert(1)</script>';
        $this->client->request('GET', '/mtracking.gif?page_url='.$xssHeader);
        $this->assertResponseStatusCodeSame(200);
        $this->client->request('GET', '/s/reports/view/'.$report->getId());
        $this->assertResponseStatusCodeSame(200);
        $this->assertStringNotContainsString($xssHeader, $this->client->getResponse()->getContent());

        $this->client->request('GET', '/s/reports/view/'.$report->getId().'/export/html');
        $this->assertStringNotContainsString($xssHeader, $this->client->getResponse()->getContent());
    }
}
